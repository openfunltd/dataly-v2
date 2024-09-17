import Diff from 'text-diff';
import fs from 'fs';
 
fs.readFile(process.argv[2], 'utf8', function(err, data){
    data = JSON.parse(data);
    let result = {};
    for (const [law, comparison] of Object.entries(data)) {
        result[law] = {};
        let current = comparison['current'];
        let commits = comparison['commits'];
        if (current === null || current == '') {
            result[law].current = current;
            result[law].commits = comparison['commits'];
            for (const [bill_idx, commit] of Object.entries(commits)) {
                result[law].commits[bill_idx] = commit.replaceAll("\n", "<br>");
            }
            continue;
        }
        result[law].current = current.replaceAll("\n", "<br>");
        result[law].commits = {};
        for (const [bill_idx, commit] of Object.entries(commits)) {
            const diff = new Diff();
            const textDiff = diff.main(current, commit);
            const diff_in_html = diff.prettyHtml(textDiff);
            result[law].commits[bill_idx] = diff_in_html;
        }
    }
    fs.writeFileSync(process.argv[3], JSON.stringify(result));
});
