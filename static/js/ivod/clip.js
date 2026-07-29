import { FFmpeg } from 'https://cdn.jsdelivr.net/npm/@ffmpeg/ffmpeg@0.12.15/dist/esm/index.js';
import { toBlobURL } from 'https://cdn.jsdelivr.net/npm/@ffmpeg/util@0.12.2/dist/esm/index.js';

const FFMPEG_CORE_BASE = 'https://cdn.jsdelivr.net/npm/@ffmpeg/core@0.12.10/dist/esm';

let ffmpegPromise = null;

function loadFFmpeg(onStatus) {
    if (!ffmpegPromise) {
        ffmpegPromise = (async () => {
            onStatus('載入剪輯引擎（約 30MB，首次使用較久）…');
            const ffmpeg = new FFmpeg();
            const [coreURL, wasmURL] = await Promise.all([
                toBlobURL(`${FFMPEG_CORE_BASE}/ffmpeg-core.js`, 'text/javascript'),
                toBlobURL(`${FFMPEG_CORE_BASE}/ffmpeg-core.wasm`, 'application/wasm'),
            ]);
            // worker.js 內部用相對路徑 import 其他模組，若用 blob URL 載入會讓那些
            // 相對匯入解析失敗，因此 worker 本體改成同源檔案（僅 ~6KB 的 glue code，
            // 真正的 ffmpeg-core 仍走 CDN 的 blob URL）。
            const classWorkerURL = `${window.location.origin}/static/js/ivod/ffmpeg/worker.js`;
            await ffmpeg.load({ coreURL, wasmURL, classWorkerURL });
            return ffmpeg;
        })();
    }
    return ffmpegPromise;
}

// "H:MM:SS,mmm" / "H:MM:SS.mmm" / "MM:SS" / plain seconds -> seconds
function parseTimecode(input) {
    const text = input.trim();
    if (text === '') {
        return NaN;
    }
    if (/^\d+(\.\d+)?$/.test(text)) {
        return parseFloat(text);
    }
    const parts = text.split(/[:,]/).map((p) => p.replace(',', '.'));
    const nums = parts.map(Number);
    if (nums.some(Number.isNaN)) {
        return NaN;
    }
    if (nums.length === 3) {
        return nums[0] * 3600 + nums[1] * 60 + nums[2];
    }
    if (nums.length === 4) {
        return nums[0] * 3600 + nums[1] * 60 + nums[2] + nums[3] / 1000;
    }
    if (nums.length === 2) {
        return nums[0] * 60 + nums[1];
    }
    return NaN;
}

function formatTimecode(totalSeconds) {
    const s = Math.max(0, totalSeconds);
    const h = Math.floor(s / 3600);
    const m = Math.floor((s / 60) % 60);
    const sec = Math.floor(s % 60);
    const ms = Math.round((s - Math.floor(s)) * 1000);
    const pad = (n, len) => String(n).padStart(len, '0');
    return `${pad(h, 2)}:${pad(m, 2)}:${pad(sec, 2)},${pad(ms, 3)}`;
}

async function resolveChunklistUrl(masterUrl) {
    const res = await fetch(masterUrl);
    if (!res.ok) {
        throw new Error(`無法讀取播放清單（HTTP ${res.status}）`);
    }
    const text = await res.text();
    const line = text
        .split('\n')
        .map((l) => l.trim())
        .find((l) => l && !l.startsWith('#'));
    if (!line) {
        throw new Error('播放清單格式無法解析');
    }
    return new URL(line, masterUrl).toString();
}

async function fetchSegments(chunklistUrl) {
    const res = await fetch(chunklistUrl);
    if (!res.ok) {
        throw new Error(`無法讀取分段清單（HTTP ${res.status}）`);
    }
    const text = await res.text();
    const lines = text.split('\n');
    const segments = [];
    let duration = null;
    for (const raw of lines) {
        const line = raw.trim();
        if (line.startsWith('#EXTINF:')) {
            duration = parseFloat(line.slice('#EXTINF:'.length).split(',')[0]);
        } else if (line && !line.startsWith('#')) {
            segments.push({ url: new URL(line, chunklistUrl).toString(), duration });
            duration = null;
        }
    }
    let cumulative = 0;
    for (const seg of segments) {
        seg.startOffset = cumulative;
        cumulative += seg.duration;
    }
    return segments;
}

function segmentsInRange(segments, start, end) {
    return segments.filter((seg) => seg.startOffset < end && seg.startOffset + seg.duration > start);
}

async function concatSegments(segments, onStatus) {
    const buffers = [];
    for (let i = 0; i < segments.length; i++) {
        onStatus(`下載片段 ${i + 1}/${segments.length}…`);
        const res = await fetch(segments[i].url);
        if (!res.ok) {
            throw new Error(`分段下載失敗（HTTP ${res.status}）`);
        }
        buffers.push(new Uint8Array(await res.arrayBuffer()));
    }
    const total = buffers.reduce((n, b) => n + b.length, 0);
    const merged = new Uint8Array(total);
    let offset = 0;
    for (const b of buffers) {
        merged.set(b, offset);
        offset += b.length;
    }
    return merged;
}

export async function clipAndDownload({ masterUrl, start, end, filename, onStatus }) {
    const status = onStatus || (() => {});
    if (!(end > start)) {
        throw new Error('結束時間必須晚於開始時間');
    }

    status('讀取播放清單…');
    const chunklistUrl = await resolveChunklistUrl(masterUrl);
    const segments = await fetchSegments(chunklistUrl);
    const covering = segmentsInRange(segments, start, end);
    if (covering.length === 0) {
        throw new Error('找不到對應時間範圍的片段');
    }

    const merged = await concatSegments(covering, status);

    const ffmpeg = await loadFFmpeg(status);

    // 從涵蓋範圍第一段的開頭起算的相對秒數；-ss 是輸入端 seek，會對齊到最近的
    // 前一個關鍵影格，因此實際畫面可能比選取的開始時間略早（通常數秒內）。
    const localStart = Math.max(0, start - covering[0].startOffset);
    const duration = end - start;

    status('剪輯中…');
    await ffmpeg.writeFile('input.ts', merged);
    await ffmpeg.exec([
        '-ss', localStart.toFixed(3),
        '-i', 'input.ts',
        '-t', duration.toFixed(3),
        '-c', 'copy',
        '-avoid_negative_ts', 'make_zero',
        '-movflags', '+faststart',
        'output.mp4',
    ]);
    const data = await ffmpeg.readFile('output.mp4');
    await ffmpeg.deleteFile('input.ts');
    await ffmpeg.deleteFile('output.mp4');

    const blob = new Blob([data.buffer], { type: 'video/mp4' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename || 'clip.mp4';
    document.body.appendChild(a);
    a.click();
    a.remove();
    setTimeout(() => URL.revokeObjectURL(url), 10000);
    status('完成！');
}

window.addEventListener('load', () => {
    const startInput = document.getElementById('clip-start');
    const endInput = document.getElementById('clip-end');
    const statusEl = document.getElementById('clip-status');
    const downloadBtn = document.getElementById('clip-download');
    if (!startInput || !endInput || !downloadBtn) {
        return;
    }

    document.getElementById('clip-start-now').addEventListener('click', () => {
        startInput.value = formatTimecode(window.video.currentTime);
    });
    document.getElementById('clip-end-now').addEventListener('click', () => {
        endInput.value = formatTimecode(window.video.currentTime);
    });

    downloadBtn.addEventListener('click', async () => {
        const start = parseTimecode(startInput.value);
        const end = parseTimecode(endInput.value);
        if (Number.isNaN(start) || Number.isNaN(end)) {
            statusEl.textContent = '請輸入正確的時間格式，例如 00:01:23,000';
            return;
        }
        downloadBtn.disabled = true;
        try {
            await clipAndDownload({
                masterUrl: window.IVOD_VIDEO_URL,
                start,
                end,
                filename: `ivod_${window.IVOD_ID}_${Math.floor(start)}-${Math.floor(end)}.mp4`,
                onStatus: (msg) => {
                    statusEl.textContent = msg;
                },
            });
        } catch (err) {
            statusEl.textContent = `失敗：${err.message}`;
        } finally {
            downloadBtn.disabled = false;
        }
    });
});
