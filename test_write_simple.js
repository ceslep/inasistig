const fs = require('fs');
const path = require('path');
const target = path.join(__dirname, 'src', 'assets', 'php', 'getDBAs.php');
const content = fs.readFileSync(target, 'utf8');
fs.writeFileSync(target, content + "\n// TEST APPEND", 'utf8');
console.log('Done');
