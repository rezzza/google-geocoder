<?php

namespace Rezzza\GoogleGeocoder\Exception;

/**
 * @author Sébastien HOUZÉ <sebastien.houze@verylastroom.com>
 */
class GoogleGeocodeException extends \Exception
{
    static public function fromStatusAndErrorMessage($status, $message = null)
    {
        switch($status) {
            case 'ZERO_RESULTS':
                $message = $message ?: 'No result found.';
                return new GoogleGeocodeNoResultException($message);
            case 'OVER_QUERY_LIMIT':
                $message = $message ?: 'Query limit exceeded.';
                return new GoogleGeocodeQuotaExceededException($message);
            case 'REQUEST_DENIED':
                $message = $message ?: 'Unauthorized request.';
                return new GoogleGeocodeRequestDeniedException($message);
            case 'INVALID_REQUEST':
                $message = $message ?: 'Invalid request.';
                return new GoogleGeocodeInvalidRequestException($message);
            case 'UNKNOWN_ERROR':
            default:
                $message = $message ?: 'Unknown error.';
                return new GoogleGeocodeUnknownException($message);
        }
    }
}
