const fs = require('fs');
const path = require('path');

const filePath = path.join(__dirname, 'src/assets/php/deepseek_php_20260223_1888f6.php');
let content = fs.readFileSync(filePath, 'utf8');

// The error is a missing '=>' after 'dba' or similar keys.
// In the text viewed: 'dba' [
content = content.replace(/'dba'\s*\[/g, "'dba' => [");

fs.writeFileSync(filePath, content, 'utf8');
console.log("Fixed deepseek syntax errors.");

// Now we can use child_process to execute the php script correctly and log errors
const { execSync } = require('child_process');
try {
    const res = execSync('php generate_missing_dbas.php', { encoding: 'utf8', stdio: 'pipe' });
    console.log("PHP Exited Correctly");
} catch (e) {
    console.error("PHP Error Details:");
    console.error(e.stderr);
    console.error(e.stdout);
}
