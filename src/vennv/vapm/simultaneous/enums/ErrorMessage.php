<?php

namespace vennv\vapm\simultaneous\enums;

enum ErrorMessage: string
{
    case FAILED_IN_FETCHING_DATA = "Error in fetching data";

    case WRONG_TYPE_WHEN_USE_CURL_EXEC = "curl_exec() should return string|false when CURL-OPT_RETURN-TRANSFER is set";

    case UNABLE_START_THREAD = "Unable to start thread";

    case DEFERRED_CALLBACK_MUST_RETURN_GENERATOR = "Deferred callback must return a Generator";

    case UNABLE_TO_OPEN_FILE = "Error: Unable to open file!";

    case FILE_DOES_NOT_EXIST = "Error: File does not exist!";

    case FILE_ALREADY_EXISTS = "Error: File already exists!";

    case CANNOT_FIND_FUNCTION_KEYWORD = "Cannot find function or fn keyword in closure";

    case CANNOT_READ_FILE = "Cannot read file";

    case INPUT_MUST_BE_STRING_OR_CALLABLE = "Input must be string or callable";

    case ERROR_TO_CREATE_SOCKET = "Error to create socket";

    case PAYLOAD_TOO_LARGE = "Payload too large";
}
