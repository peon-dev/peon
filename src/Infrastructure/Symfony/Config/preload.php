<?php

$fileName = dirname(__DIR__, 4).'/var/cache/prod/Peon_Infrastructure_Symfony_PeonKernelProdContainer.preload.php';

if (file_exists($fileName)) {
    require $fileName;
}
