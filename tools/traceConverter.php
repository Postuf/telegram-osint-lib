<?php

declare(strict_types=1);

use Helpers\TraceConverter\TraceConverterTextToJson;

require_once __DIR__.'/../vendor/autoload.php';

(new class() {
    /**
     * Run command.
     */
    public function run(): void
    {
        $commandLineParameters = $this->parseCommandLineParameters();
        if ($commandLineParameters === null) {
            $this->printHelp();

            exit(1);
        }

        [$inputPattern, $outputFolderOrNull] = $commandLineParameters;

        $converter = new TraceConverterTextToJson();
        foreach ($this->getFilePaths($inputPattern) as $inputPath) {
            $outputPath = $this->composeJsonFileNameFromTextOne($inputPath, $outputFolderOrNull);
            $outputContent = $converter->convert($inputPath);

            file_put_contents($outputPath, $outputContent);
        }
    }

    /**
     * @param string $pattern
     * @param int    $flags
     *
     * @return iterable|Traversable|string[]
     */
    private function getFilePaths(string $pattern, int $flags = GLOB_BRACE): iterable
    {
        $pathsOrFalse = glob($pattern, $flags);

        if ($pathsOrFalse === false) {
            throw new InvalidArgumentException('Invalid input files path.');
        }

        assert(is_array($pathsOrFalse));

        yield from $pathsOrFalse;
    }

    /**
     * @param string      $filePath
     * @param string|null $folderPathOrNull
     *
     * @return string
     */
    private function composeJsonFileNameFromTextOne(string $filePath, ?string $folderPathOrNull): string {
        assert(file_exists($filePath));

        ['dirname' => $folderName, 'filename' => $fileName] = pathinfo(realpath($filePath));

        $folderName = $folderPathOrNull ?? $folderName;

        return $folderName.DIRECTORY_SEPARATOR.$fileName.'.json';
    }

    /**
     * @return array|null
     */
    private function parseCommandLineParameters(): ?array
    {
        $argsOrFalse = getopt('i:o:h', ['input-pattern:', 'output-folder', 'help']);
        if ($argsOrFalse === false
            || (array_key_exists('h', $argsOrFalse) || array_key_exists('help', $argsOrFalse))
            || (!array_key_exists('i', $argsOrFalse) && !array_key_exists('input-pattern', $argsOrFalse))
        ) {
            return null;
        }

        $inputPattern = $argsOrFalse['i'] ?? $argsOrFalse['input-pattern'];
        $outputFolder = $argsOrFalse['o'] ?? $argsOrFalse['output-folder'] ?? null;

        assert($outputFolder === null || file_exists($outputFolder));

        return [$inputPattern, $outputFolder];
    }

    /**
     * Print command line help.
     */
    private function printHelp(): void
    {
        echo <<<'EOT'
Usage:
    php traceConverter.php -i input-file-pattern [-o output-folder]
    php traceConverter.php --input-pattern input-file-pattern [--output-folder output-folder]

   -i, --input-pattern          Input file pattern (e.g file name).
   -o, --output-folder          Optional output folder. By default it will be input file folder.
   -h, --help                   Display this help message.

EOT;
    }
})->run();
