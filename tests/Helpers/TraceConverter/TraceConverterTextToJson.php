<?php

declare(strict_types=1);

namespace Helpers\TraceConverter;

use Helpers\TraceConverter\Contracts\TraceInterface;
use Helpers\TraceConverter\Traces\Trace;
use Helpers\TraceConverter\Traces\TraceRecord;
use Helpers\TraceSocketMessenger;
use InvalidArgumentException;
use JsonException;

class TraceConverterTextToJson
{
    /**
     * Read the file and convert text trace to JSON.
     *
     * @param string $pathToTxtFile
     *
     * @throws JsonException
     *
     * @return string
     */
    public function convert(string $pathToTxtFile): string
    {
        $trace = $this->readTraceFromTextFile($pathToTxtFile);

        $jsonOrFalse = json_encode($trace, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        if ($jsonOrFalse === false) {
            throw new InvalidArgumentException("Failed to decode `$pathToTxtFile`.");
        }

        return $jsonOrFalse;
    }

    /**
     * @param string $filePath
     *
     * @throws JsonException
     *
     * @return TraceInterface
     */
    private function readTraceFromTextFile(string $filePath): TraceInterface
    {
        if (!is_file($filePath)) {
            throw new InvalidArgumentException("Trace file `$filePath` do not exists.");
        }

        $fileContentOrFalse = file_get_contents($filePath);
        if ($fileContentOrFalse === false) {
            throw new InvalidArgumentException("Trace file `$filePath` is not readable.");
        }

        $decodedFileContent = json_decode($fileContentOrFalse, false, 512, JSON_THROW_ON_ERROR);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException("Trace file `$filePath` does not look like a valid JSON file.");
        }

        if (count($decodedFileContent) !== 2) {
            throw new InvalidArgumentException("Trace file `$filePath` has invalid file format.");
        }

        [$traceStartTimestamp, $encodedTraceSteps] = $decodedFileContent;
        if (!is_numeric($traceStartTimestamp) || !is_array($encodedTraceSteps)) {
            throw new InvalidArgumentException("Trace file `$filePath` has invalid file format.");
        }

        $traceRecords = [];
        foreach ($encodedTraceSteps as $encodedTraceStep) {
            if (!is_array($encodedTraceStep) || count($encodedTraceStep) !== 3) {
                throw new InvalidArgumentException("Trace file `$filePath` has invalid trace step format.");
            }
            [$recordType, $recordHexContent, $recordTimeStamp] = $encodedTraceStep;
            if (!is_string($recordType) || !is_string($recordHexContent) || !is_numeric($recordTimeStamp)) {
                throw new InvalidArgumentException("Trace file `$filePath` has invalid trace step format.");
            }
            $recordContentOrFalse = hex2bin($recordHexContent);
            if ($recordContentOrFalse === false) {
                throw new InvalidArgumentException("Trace file `$filePath` has invalid trace step hex values.");
            }

            $message = TraceSocketMessenger::unserializeAnonymousMessage($recordContentOrFalse);

            $traceRecords[] = new TraceRecord($recordType, $message, $recordTimeStamp);
        }

        return new Trace($traceStartTimestamp, $traceRecords);
    }
}
