// Optional static checks only. NOT a replacement for PHP lint or PHPUnit.
import fs from 'node:fs';
import path from 'node:path';
import assert from 'node:assert/strict';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const read = (file) => fs.readFileSync(path.join(root, file), 'utf8');
const walk = (folder) => fs.readdirSync(path.join(root, folder), { withFileTypes: true }).flatMap((entry) => entry.isDirectory() ? walk(`${folder}/${entry.name}`) : [`${folder}/${entry.name}`]);
let checks = 0;
const check = (condition, message) => { assert.ok(condition, message); checks++; };

for (const file of ['composer.json', 'composer.lock', 'package.json']) {
    JSON.parse(read(file)); checks++;
}
const routes = read('routes/web.php');
const adminStart = routes.indexOf("Route::prefix('admin')");
const names = new Set();
for (const match of routes.matchAll(/->name\('([^']+)'\)/g)) {
    const name = (match.index > adminStart ? 'admin.' : '') + match[1];
    if (match[1] !== 'admin.') names.add(name);
}
for (const match of routes.matchAll(/Route::resource\('([^']+)'[^;]+;/g)) {
    const actions = match[0].includes("->only('index', 'show', 'update')") ? ['index', 'show', 'update'] : ['index', 'create', 'store', 'edit', 'update', 'destroy'];
    for (const action of actions) names.add(`admin.${match[1]}.${action}`);
}
const sourceFiles = ['routes/web.php', 'bootstrap/app.php', ...walk('app'), ...walk('resources/views')].filter((file) => file.endsWith('.php'));
for (const file of sourceFiles) {
    const text = read(file);
    for (const match of text.matchAll(/(?<!->)\broute\('([^']+)'/g)) check(names.has(match[1]), `${file}: route missing ${match[1]}`);
    for (const match of text.matchAll(/(?:\bview\(|@include\(|@extends\()'([^']+)'/g)) check(fs.existsSync(path.join(root, 'resources/views', match[1].replaceAll('.', '/') + '.blade.php')), `${file}: view missing ${match[1]}`);
}
const pairs = { endif: 'if', endforeach: 'foreach', endforelse: 'forelse', endauth: 'auth', endguest: 'guest', endsection: 'section' };
const viewFiles = walk('resources/views').filter((file) => file.endsWith('.blade.php'));
for (const file of viewFiles) {
    const content = read(file);
    const stack = [];
    for (const match of content.matchAll(/@(if|endif|foreach|endforeach|forelse|endforelse|auth|endauth|guest|endguest|section|endsection)\b(?:\(([^\n]*?)\))?/g)) {
        const directive = match[1];
        if (directive === 'section' && match[2]?.includes(',')) continue;
        if (pairs[directive]) check(stack.pop() === pairs[directive], `${file}: unbalanced @${directive}`);
        else stack.push(directive);
    }
    check(stack.length === 0, `${file}: unclosed Blade block`);
    for (const match of content.matchAll(/<form\b[^>]*>[\s\S]*?<\/form>/g)) {
        if (/method="POST"/i.test(match[0])) check(match[0].includes('@csrf'), `${file}: POST form missing @csrf`);
    }
    check(!content.includes('{!!'), `${file}: unexpected unescaped Blade output`);
}

// Lexical delimiter check; deliberately does not claim to parse PHP syntax.
const phpFiles = ['app', 'routes', 'database', 'config', 'tests', 'bootstrap', 'lang'].flatMap(walk).filter((file) => file.endsWith('.php'));
for (const file of phpFiles) {
    let text = read(file);
    const cleaned = text.replace(/\/\*[\s\S]*?\*\/|\/\/[^\n]*|'(?:\\[\s\S]|[^'\\])*'|"(?:\\[\s\S]|[^"\\])*"/g, '');
    const stack = [];
    const closes = { ')': '(', ']': '[', '}': '{' };
    for (const char of cleaned) {
        if ('([{'.includes(char)) stack.push(char);
        else if (')]}'.includes(char)) check(stack.pop() === closes[char], `${file}: unbalanced delimiter ${char}`);
    }
    check(stack.length === 0, `${file}: unclosed PHP delimiter`);
}
check(read('.env.example').includes('APP_KEY=\n'), 'Example must not ship APP_KEY');
check(read('.env.example').includes('DB_CONNECTION=mysql'), 'Default DB must be MySQL');
check(!/\brole\b/.test(read('app/Models/User.php').match(/#\[Fillable\(.*?\)\]/s)?.[0] ?? ''), 'Role must not be publicly fillable');
check(read('app/Services/OrderService.php').includes('lockForUpdate'), 'Checkout locking missing');
const testCount = walk('tests').reduce((sum, file) => sum + ((read(file).match(/public function test_/g) ?? []).length), 0);
console.log(JSON.stringify({ result: 'STATIC CHECKS PASSED', checks, phpFiles: phpFiles.length, bladeViews: viewFiles.length, namedRoutes: names.size, testMethods: testCount, runtimeTests: 'NOT RUN: PHP/Composer unavailable in authoring environment' }, null, 2));
