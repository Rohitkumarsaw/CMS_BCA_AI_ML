<?php
spl_autoload_register(function ($class) {
    // Dompdf namespace -> dompdf/src/
    if (strncmp('Dompdf\\', $class, 7) === 0) {
        if ($class === 'Dompdf\\Cpdf') {
            $file = __DIR__ . '/lib/Cpdf.php';
            if (file_exists($file)) { require $file; return; }
        }
        $file = __DIR__ . '/src/' . str_replace('\\', '/', substr($class, 7)) . '.php';
        if (file_exists($file)) { require $file; return; }
    }
    // Masterminds namespace -> html5-php/src/
    if (strncmp('Masterminds\\', $class, 12) === 0) {
        $file = __DIR__ . '/../html5-php/src/' . str_replace('\\', '/', substr($class, 12)) . '.php';
        if (file_exists($file)) { require $file; return; }
    }
    // FontLib namespace -> php-font-lib/src/FontLib/
    if (strncmp('FontLib\\', $class, 8) === 0) {
        $file = __DIR__ . '/../php-font-lib/src/FontLib/' . str_replace('\\', '/', substr($class, 8)) . '.php';
        if (file_exists($file)) { require $file; return; }
    }
});
