import fs from 'fs';
const data = fs.readFileSync('debug_horarios.csv', 'utf8');
const lines = data.split('\n');

const cesarLine = lines.find(l => l.includes('"CESAR"') || l.startsWith('"CESAR'));
if (cesarLine) {
  const cols = cesarLine.split('","');
  console.log('Total columns for CESAR:', cols.length);
  console.log('\nColumns:');
  cols.slice(0, 45).forEach((c, i) => {
    console.log((i+1) + ': [' + c.replace(/"/g, '').replace(/\n/g, ' ') + ']');
  });
} else {
  console.log('CESAR row not found');
  console.log('Line count:', lines.length);
  console.log('First few lines:');
  lines.slice(0, 20).forEach((l, i) => console.log(i + ': ' + l.substring(0, 100)));
}