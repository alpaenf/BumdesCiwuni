const fs = require('fs');
const path = require('path');

const folders = [
    'c:\\laragon\\www\\simpan-pinjam\\resources\\js\\Pages\\Wifi',
    'c:\\laragon\\www\\simpan-pinjam\\resources\\js\\Pages\\ketahanan pangan',
    'c:\\laragon\\www\\simpan-pinjam\\resources\\js\\Pages\\Perdagangan Besar'
];

function replaceColors(content, isDark) {
    let c = content;
    
    if (isDark) {
        c = c.replace(/bg-slate-950/g, 'bg-slate-50');
        c = c.replace(/bg-slate-900/g, 'bg-white');
        c = c.replace(/bg-slate-850/g, 'bg-slate-100');
        c = c.replace(/bg-slate-800/g, 'bg-slate-100');
        c = c.replace(/border-slate-900/g, 'border-slate-200');
        c = c.replace(/border-slate-850/g, 'border-slate-300');
        c = c.replace(/border-slate-800/g, 'border-slate-200');
        
        c = c.replace(/text-slate-100/g, 'text-slate-800');
        c = c.replace(/text-slate-200/g, 'text-slate-700');
        c = c.replace(/text-slate-300/g, 'text-slate-600');
        c = c.replace(/text-slate-350/g, 'text-slate-600');
        c = c.replace(/text-slate-400/g, 'text-slate-500');
        c = c.replace(/text-slate-450/g, 'text-slate-500');
        
        // Text white to slate-900, but preserve text-white inside buttons (bg-xxx)
        c = c.replace(/text-white(?!\s+bg-)/g, 'text-slate-900');
    } else {
        c = c.replace(/stone-/g, 'slate-');
    }
    
    // Replace indigo (Wifi)
    c = c.replace(/-indigo-/g, '-blue-');
    c = c.replace(/from-indigo-/g, 'from-blue-');
    c = c.replace(/to-purple-/g, 'to-blue-');
    c = c.replace(/to-pink-/g, 'to-blue-');
    c = c.replace(/via-purple-/g, 'via-blue-');
    
    // Replace emerald (Pangan)
    c = c.replace(/-emerald-950/g, '-blue-900');
    c = c.replace(/-emerald-900/g, '-blue-800');
    c = c.replace(/-emerald-800/g, '-blue-700');
    c = c.replace(/-emerald-750/g, '-blue-700');
    c = c.replace(/-emerald-700/g, '-blue-600');
    c = c.replace(/-emerald-650/g, '-blue-500');
    c = c.replace(/-emerald-600/g, '-blue-600');
    c = c.replace(/-emerald-500/g, '-blue-500');
    c = c.replace(/-emerald-400/g, '-blue-400');
    c = c.replace(/-emerald-300/g, '-blue-300');
    c = c.replace(/-emerald-250/g, '-blue-200');
    c = c.replace(/-emerald-200/g, '-blue-200');
    c = c.replace(/-emerald-150/g, '-blue-200');
    c = c.replace(/-emerald-100/g, '-blue-100');
    c = c.replace(/-emerald-50/g, '-blue-50');
    
    // Replace amber
    c = c.replace(/-amber-600/g, '-blue-600');
    c = c.replace(/-amber-500/g, '-blue-500');
    c = c.replace(/-amber-400/g, '-blue-500');
    c = c.replace(/-amber-300/g, '-blue-600');
    c = c.replace(/-amber-50/g, '-blue-50');
    
    // Fix contrast issues
    c = c.replace(/bg-blue-600 hover:bg-blue-500 text-slate-900/g, 'bg-blue-600 hover:bg-blue-500 text-white');
    c = c.replace(/bg-blue-600 hover:bg-blue-700 text-slate-900/g, 'bg-blue-600 hover:bg-blue-700 text-white');
    c = c.replace(/bg-blue-600 text-slate-900/g, 'bg-blue-600 text-white');
    c = c.replace(/bg-blue-500 text-slate-900/g, 'bg-blue-500 text-white');
    
    return c;
}

function processDirectory(dirPath, isDark) {
    const files = fs.readdirSync(dirPath);
    for (const file of files) {
        const fullPath = path.join(dirPath, file);
        if (fs.statSync(fullPath).isDirectory()) {
            processDirectory(fullPath, isDark);
        } else if (fullPath.endsWith('.vue')) {
            let content = fs.readFileSync(fullPath, 'utf8');
            let updated = replaceColors(content, isDark);
            
            // Few final tweaks for text-white replacements that might be grouped
            updated = updated.replace(/text-slate-900 font-bold text-xs rounded-xl transition/g, 'text-white font-bold text-xs rounded-xl transition');
            updated = updated.replace(/text-slate-900 bg-blue-/g, 'text-white bg-blue-');
            
            fs.writeFileSync(fullPath, updated, 'utf8');
        }
    }
}

for (const folder of folders) {
    const isDark = folder.includes('Wifi') || folder.includes('Perdagangan Besar');
    processDirectory(folder, isDark);
}

console.log('Themes perfectly synchronized!');
