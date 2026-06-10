# Dataly v2 — 架構說明與修改指南

## 專案簡介

立院統合資料網（Dataly）是一個整合台灣立法院多種公開資料的查詢入口，透過呼叫 `ly.govapi.tw/v2` API，提供議案、委員、iVOD、公報等資料的瀏覽與搜尋介面。

---

## 目錄結構

```
dataly-v2/
├── index.php                 # 入口點，呼叫 MiniEngine::dispatch()
├── init.inc.php              # 初始化：include framework、config、設定 include_path
├── mini-engine.php           # 自製輕量 MVC 框架（14KB，含路由、View、自動載入）
├── config.sample.inc.php     # 設定範本（實際設定存於 config.inc.php，不進版控）
├── .htaccess                 # Apache URL rewrite，所有非靜態檔案導向 index.php
├── package.json              # npm 相依：text-diff（法條比對用）
│
├── controllers/
│   ├── IndexController.php   # 首頁、robots.txt
│   ├── CollectionController.php  # 主要資料瀏覽邏輯（list / item）
│   └── ErrorController.php   # 例外攔截與錯誤顯示
│
├── libraries/
│   ├── LYAPI.php             # Legislative Yuan API 封裝（cURL）
│   ├── TypeHelper.php        # 核心：所有資料類型的設定中心
│   ├── MeetSubjectHelper.php # 會議名稱解析（中文數字轉換等）
│   ├── LawDiffHelper.php     # 法條新舊版本比對邏輯
│   ├── PartyHelper.php       # 政黨 icon URL 對應
│   └── MiniEngineHelper.php  # 雜項工具（隨機字串等）
│
├── views/
│   ├── layout/app.php        # 主版型：sidebar、Bootstrap 框架、全域 JS/CSS
│   ├── index/index.php       # 首頁
│   ├── collection/
│   │   ├── list.php          # 清單頁 tab 架構
│   │   ├── item.php          # 詳情頁 tab 架構
│   │   ├── table.php         # DataTable 元件（含 server-side 分頁、過濾、巢狀欄位支援）
│   │   ├── rawdata.php       # 原始 JSON 展示
│   │   ├── bill_data.php     # 議案詳情
│   │   ├── bill_law-diff.php # 法條對照
│   │   ├── bill_related-document.php
│   │   ├── legislator_data.php
│   │   ├── legislator_list.php
│   │   ├── meet_data.php
│   │   ├── meet_gazette.php
│   │   ├── meet_opendata.php
│   │   ├── meet_proceedings.php
│   │   ├── law_content_data.php
│   │   ├── gazette_data.php
│   │   ├── gazette_agenda_data.php
│   │   ├── gazette_agenda_content.php
│   │   ├── interpellation_data.php
│   │   ├── ivod_date.php
│   │   ├── ivod_datelist.php
│   │   ├── ivod_gazette.php
│   │   ├── ivod_ai-transcript.php
│   │   └── vote_data.php         # 投票記錄詳情
│   └── partial/tooltip.php
│
└── static/
    ├── css/                  # SB Admin 2、tooltip、自訂樣式
    ├── js/                   # SB Admin 2、DataTables 整合、iVOD、法條比對
    └── img/party_icon/       # 政黨 icon（DPP, KMT, TPP, None）
```

---

## 技術棧

| 層面 | 技術 |
|------|------|
| 後端語言 | PHP 7+ |
| MVC 框架 | MiniEngine（自製，in-repo） |
| CSS 框架 | SB Admin 2（Bootstrap 4-based） |
| 表格元件 | DataTables（Bootstrap 整合） |
| Icons | FontAwesome 6.6.0 |
| 影音串流 | HLS.js（iVOD） |
| 法條比對 | text-diff（npm） |
| API 呼叫 | cURL |
| Web Server | Apache（.htaccess URL rewrite） |

**無資料庫**：完全是 API-consumer 架構，所有資料來自 `ly.govapi.tw/v2`。

---

## 核心架構

### 1. MiniEngine 路由

URL 格式：`/[controller]/[action]/[param1]/[param2]/...`

```
/                    → IndexController::indexAction()
/collection/list/bill → CollectionController::listAction('bill')
/collection/item/bill/123 → CollectionController::itemAction('bill', '123')
```

- 類別自動載入：`_` 和 `\` 轉換為目錄分隔符（PSR-0 慣例）
- View 使用 `yield_start/yield_end` 定義插槽，`partial()` 嵌入子版型

### 2. TypeHelper — 資料類型中心

`libraries/TypeHelper.php` 是整個應用最重要的設定檔，定義了 11 種資料類型：

| type | 名稱 | 說明 |
|------|------|------|
| meet | 會議 | 院會/委員會會議 |
| bill | 議案 | 法案、提案 |
| legislator | 立委 | 立法委員 |
| committee | 委員會 | |
| ivod | iVOD | 立院影音直播 |
| law | 法律 | 現行法規 |
| law_content | 法律條文 | 個別條文 |
| gazette | 公報 | 立院公報 |
| gazette_agenda | 公報章節 | |
| interpellation | 書面質詢 | |
| vote | 投票記錄 | 院會/委員會表決記錄（API: `/votes`） |

每個 type 設定包含：
- `default_aggs`：預設聚合欄位（側邊過濾器）。**注意**：必須是該 type 對應 API 支援 `agg=` 的欄位，否則 API 回傳錯誤。
- `collection_features`：清單頁可用 tab（table、list、date、datelist 等）
- `item_features`：詳情頁可用 tab（data、law-diff、related-document 等）
- `cols`：DataTable 顯示欄位。支援 dot notation 存取巢狀欄位（如 `表決結果.贊成人數`）。

#### TypeHelper 命名慣例

type key 一律用**單數**，框架自動衍生：
- 清單 API URL：`{host}/{type}s`（加 s）
- 單筆 API URL：`/{type}/{id}`（單數）
- API 回應資料 key：`{type}s`（加 s，`getDataColumn()` 使用）

### 3. 動態 Tab 派發機制

Controller 讀取 TypeHelper 設定後，依 tab 名稱自動派發：

```
URL: /collection/item/bill/123/law-diff
→ 先找 CollectionController::item_bill_law-diff()
→ 若無，找 view: views/collection/bill_law-diff.php
```

### 4. DataTable 巢狀欄位支援

`table.php` 的 JS 使用 `getNestedValue(record, path)` 以 dot notation 存取巢狀物件：

```javascript
// col = '表決結果.贊成人數'
// record = { 表決結果: { 贊成人數: 40 } }
getNestedValue(record, col)  // → 40
```

TypeHelper `cols` 直接填 `表決結果.贊成人數` 即可，`table.php` 會自動處理。

### 5. LYAPI — API 呼叫

```php
LYAPI::query('/bills', [
    'agg' => 'billStatus',
    'filter' => 'term=123',
    'limit' => 50,
]);
```

預設 host：`https://ly.govapi.tw/v2`，可透過環境變數 `LYAPI_HOST` 覆寫。

---

## 各 type API 欄位備註

### vote（投票記錄）— `/votes`

API 回傳欄位（確認）：

| 欄位 | 說明 |
|------|------|
| 屆 | 屆別 |
| 會議代碼 | 對應 meet type 的 ID |
| 公報文件代碼 | |
| 行號 | |
| 會議名稱 | |
| 表決型態 | |
| 表決時間 | |
| 表決議題 | 表決事項說明 |
| 表決結果 | 巢狀物件，含 出席人數、贊成人數、反對人數、棄權人數 |
| 投票委員 | 陣列，所有出席委員姓名 |
| 贊成 | 陣列，贊成委員姓名 |

**限制**：`/votes` API 支援 `agg=屆`、`agg=投票委員`，但不支援所有欄位的 agg（如 `agg=表決型態` 可能不支援，使用前需確認）。

---

## 修改指南

### A. 新增一種資料類型

1. **在 `TypeHelper.php` 新增 type 設定**：
   ```php
   'new_type' => [
       'name' => '名稱',
       'icon' => 'fas fa-fw fa-icon-name',
       'cols' => ['欄位1', '欄位2', '巢狀.欄位'],  // 支援 dot notation
       'default_aggs' => ['欄位1'],  // 只填該 API 確認支援 agg= 的欄位
       'item_features' => ['data' => '資料'],
   ]
   ```
2. **新增詳情 view**：`views/collection/new_type_data.php`
3. Sidebar 會自動顯示（`layout/app.php` 讀 TypeHelper）

> **重要**：新增 type 前先用瀏覽器確認 `{host}/{type}s?agg={欄位}` 不會報錯，再加入 `default_aggs`。

### B. 新增現有類型的 Tab

1. 在 TypeHelper 的 `item_features` 加入新 tab key 和名稱
2. 新增對應 view 檔：`views/collection/{type}_{tab}.php`
3. 若需要特殊邏輯，在 `CollectionController.php` 新增方法：
   ```php
   protected function item_{type}_{tab}() { ... }
   ```

### C. 修改表格欄位

找到 `TypeHelper.php` 中對應 type 的 `cols` 陣列，調整欄位名稱。巢狀欄位用 `父欄位.子欄位` 格式，`table.php` 會自動處理。

### D. 新增側邊過濾器

在 TypeHelper 的 `default_aggs` 加入欄位名稱（**必須**先確認 API 支援該欄位的 `agg=` 參數）。

### E. 修改 API 呼叫邏輯

- 簡單查詢：調整 `TypeHelper.php` 的 `cols`/`default_aggs`
- 複雜邏輯：在 `CollectionController.php` 覆寫對應方法
- API 封裝：`libraries/LYAPI.php`

### F. 新增頁面（非 collection 類型）

1. 新增 Controller：`controllers/NewController.php`，繼承 `MiniEngineController`
2. 新增 View 目錄：`views/new/`，建立對應 action view
3. 不需修改路由設定（MiniEngine 自動路由）

### G. 調整版面/樣式

- 全域：`views/layout/app.php`、`static/css/sb-admin-2.min.css`
- 特定功能：`static/css/bill/`、`static/css/ivod/` 等對應目錄

---

## 環境設定

| 變數 | 預設值 | 說明 |
|------|--------|------|
| `LYAPI_HOST` | `ly.govapi.tw/v2` | API 端點 |
| `APP_NAME` | `Mini Engine sample application` | 應用名稱 |
| `ENV` | （未設定） | 設為 `production` 時顯示精簡錯誤頁 |

設定方式：複製 `config.sample.inc.php` 為 `config.inc.php`，加入需要的 `putenv()` 呼叫。

---

## 注意事項

- `config.inc.php` 已在 `.gitignore`，勿將密鑰或環境設定直接寫在版控檔案
- `node_modules/` 也在 `.gitignore`，部署時需執行 `npm install`
- 所有 PHP 類別透過 include_path 自動載入，新增類別放在 `libraries/` 即可直接 `new ClassName()`
- iVOD 相關功能有自己的 JS 檔（`static/js/ivod/`），修改時注意對應關係
- 新增 type 的 `default_aggs` 前，務必手動確認 API 支援該欄位的 `agg=` 參數，否則整個列表頁會失敗
