const fs = require('fs');
const path = require('path');
const sourcePath = path.join(__dirname, 'src', 'assets', 'php', 'deepseek_php_20260223_1888f6.php');
try {
    const content = fs.readFileSync(sourcePath, 'utf8');
    console.log('Read success, length:', content.length);
    console.log('Start:', content.substring(0, 100));
} catch (e) {
    console.error('Error reading:', e.message);
}
