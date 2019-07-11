<?php


namespace Kinikit\MVC\Request;


class FileUpload {

    /**
     * @var string
     */
    private $parameterName;


    /**
     * @var string
     */
    private $clientFilename;


    /**
     * @var string
     */
    private $mimeType;


    /**
     * @var integer
     */
    private $size;


    /**
     * @var string
     */
    private $temporaryFilePath;


    /**
     * @var string
     */
    private $status;


    /**
     * @var string
     */
    private $failureReason;


    // Status
    const STATUS_SUCCESS = "SUCCESS";
    const STATUS_FAILURE = "FAILED";
    const STATUS_UNKNOWN = "UNKNOWN";

    // Failure reasons
    const FAILED_FILE_TOO_LARGE_FOR_SERVER = "FILE_TOO_LARGE_FOR_SERVER";
    const FAILED_FILE_TOO_LARGE_FOR_CLIENT = "FILE_TOO_LARGE_FOR_CLIENT";
    const FAILED_PARTIALLY_UPLOADED = "PARTIALLY_UPLOADED";
    const FAILED_NO_FILE = "NO_FILE";
    const FAILED_MISSING_TEMP_DIRECTORY = "MISSING_TEMP_DIRECTORY";
    const FAILED_DISK_ERROR = "DISK_ERROR";

    private const STATUS_MAPPINGS = [
        UPLOAD_ERR_INI_SIZE => self::FAILED_FILE_TOO_LARGE_FOR_SERVER,
        UPLOAD_ERR_FORM_SIZE => self::FAILED_FILE_TOO_LARGE_FOR_CLIENT,
        UPLOAD_ERR_PARTIAL => self::FAILED_PARTIALLY_UPLOADED,
        UPLOAD_ERR_NO_FILE => self::FAILED_NO_FILE,
        UPLOAD_ERR_NO_TMP_DIR => self::FAILED_MISSING_TEMP_DIRECTORY,
        UPLOAD_ERR_CANT_WRITE => self::FAILED_DISK_ERROR
    ];

    /**
     * Construct with a raw file from $_FILES collection.
     *
     * FileUpload constructor.
     *
     * @param string $parameterName
     * @param array $rawFile
     */
    public function __construct($parameterName, $rawFile) {
        $this->parameterName = $parameterName;
        $this->clientFilename = isset($rawFile["name"]) ? $rawFile["name"] : null;
        $this->mimeType = isset($rawFile["type"]) ? $rawFile["type"] : null;
        $this->size = isset($rawFile["size"]) ? $rawFile["size"] : null;
        $this->temporaryFilePath = isset($rawFile["tmp_name"]) ? $rawFile["tmp_name"] : null;
        if (isset($rawFile["error"])) {
            $this->status = $rawFile["error"] ? self::STATUS_FAILURE : self::STATUS_SUCCESS;
            if ($this->status == self::STATUS_FAILURE) {
                $this->failureReason = self::STATUS_MAPPINGS[$rawFile["error"]];
            }
        } else {
            $this->status = self::STATUS_UNKNOWN;
        }

    }


    /**
     * Get the name used to reference this file in the request (usually form name).
     *
     * @return string
     */
    public function getParameterName() {
        return $this->parameterName;
    }

    /**
     * Get the original filename as uploaded.
     *
     * @return string
     */
    public function getClientFilename(): string {
        return $this->clientFilename;
    }


    /**
     * Get the mime type for the file.
     *
     * @return string
     */
    public function getMimeType() {
        return $this->mimeType;
    }

    /**
     * Get the size in bytes for this file.
     *
     * @return int
     */
    public function getSize() {
        return $this->size;
    }

    /**
     * Get the temporary file path for this file.
     *
     * @return string
     */
    public function getTemporaryFilePath() {
        return $this->temporaryFilePath;
    }

    /**
     * Get the status for this file.  One of the constants above
     *
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Get a failure reason if failed.
     *
     * @return string
     */
    public function getFailureReason() {
        return $this->failureReason;
    }


}
