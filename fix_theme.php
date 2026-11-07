<?php
$files = [
    'resources/views/organizer/events/index.blade.php',
    'resources/views/organizer/dashboard.blade.php',
    'resources/views/scanner/index.blade.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    
    // Light theme replacements
    $content = str_replace('bg-slate-950', 'bg-gray-50', $content);
    $content = str_replace('text-slate-100', 'text-gray-900', $content);
    $content = str_replace('text-slate-200', 'text-gray-800', $content);
    $content = str_replace('border-slate-800', 'border-gray-200', $content);
    $content = str_replace('text-white', 'text-gray-900', $content);
    $content = str_replace('text-slate-400', 'text-gray-500', $content);
    $content = str_replace('text-slate-500', 'text-gray-400', $content);
    $content = str_replace('bg-slate-900/60', 'bg-white', $content);
    $content = str_replace('bg-slate-900/50', 'bg-white', $content);
    $content = str_replace('bg-slate-950/80', 'bg-gray-100', $content);
    $content = str_replace('text-slate-300', 'text-gray-700', $content);
    $content = str_replace('divide-slate-800/60', 'divide-gray-100', $content);
    $content = str_replace('hover:bg-slate-800/30', 'hover:bg-gray-50', $content);
    $content = str_replace('bg-slate-800', 'bg-gray-100', $content);
    $content = str_replace('border-slate-700', 'border-gray-200', $content);
    $content = str_replace('border-slate-700/50', 'border-gray-200', $content);
    $content = str_replace('text-indigo-300', 'text-indigo-600', $content);
    $content = str_replace('bg-indigo-600/20', 'bg-indigo-100', $content);
    
    // Scanner specifics
    $content = str_replace('bg-black/50', 'bg-gray-200', $content);
    $content = str_replace('bg-slate-950/50', 'bg-gray-50', $content);
    
    // Fix buttons that were originally text-white (we changed all text-white to text-gray-900 earlier)
    $content = str_replace('text-gray-900 font-bold rounded-xl text-xs transition shadow-lg', 'text-white font-bold rounded-xl text-xs transition shadow-lg', $content);
    $content = str_replace('text-gray-900 font-bold rounded-xl text-sm transition', 'text-white font-bold rounded-xl text-sm transition', $content);
    $content = str_replace('text-gray-900 text-center tracking-tight', 'text-white text-center tracking-tight', $content);
    $content = str_replace('hover:text-gray-900', 'hover:text-white', $content);

    // Event Index specific fixes (like the hover on action buttons, or delete button)
    $content = str_replace('text-rose-400', 'text-rose-600', $content);
    $content = str_replace('bg-rose-500/10', 'bg-rose-100', $content);
    $content = str_replace('text-emerald-400', 'text-emerald-600', $content);
    $content = str_replace('bg-emerald-600/20', 'bg-emerald-100', $content);
    $content = str_replace('bg-emerald-500/10', 'bg-emerald-50', $content);
    
    file_put_contents($file, $content);
}
echo "Theme updated!\n";
