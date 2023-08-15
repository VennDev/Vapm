<?php

/**
 * Vapm - A library support for PHP about Async, Promise, Coroutine, Thread, GreenThread
 *          and other non-blocking methods. The library also includes some Javascript packages
 *          such as Express. The method is based on Fibers & Generator & Processes, requires
 *          you to have php version from >= 8.1
 *
 * Copyright (C) 2023  VennDev
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

declare(strict_types = 1);

namespace vennv\vapm\http;

final class TypeData {

    public const JSON = "application/json";

    public const XML = "application/xml";

    public const HTML = "text/html";

    public const TEXT = "text/plain";

    public const FORM = "application/x-www-form-urlencoded";

    public const MULTIPART = "multipart/form-data";

    public const STREAM = "application/octet-stream";

    public const PDF = "application/pdf";

    public const ZIP = "application/zip";

    public const GZIP = "application/gzip";

    public const TAR = "application/x-tar";

    public const RAR = "application/vnd.rar";

    public const DOC = "application/msword";

    public const DOCX = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";

    public const XLS = "application/vnd.ms-excel";

    public const XLSX = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";

    public const PPT = "application/vnd.ms-powerpoint";

    public const PPTX = "application/vnd.openxmlformats-officedocument.presentationml.presentation";

    public const MP3 = "audio/mpeg";

    public const MP4 = "video/mp4";

    public const AVI = "video/x-msvideo";

    public const MOV = "video/quicktime";

    public const FLV = "video/x-flv";

    public const PNG = "image/png";

    public const JPG = "image/jpeg";

    public const GIF = "image/gif";

    public const ICO = "image/x-icon";

    public const SVG = "image/svg+xml";

    public const TIFF = "image/tiff";

    public const WEBP = "image/webp";

    public const BMP = "image/bmp";

    public const WEBM = "video/webm";

    public const OGG = "audio/ogg";

    public const OGV = "video/ogg";

    public const WEBA = "audio/webm";

    public const WEBVTT = "text/vtt";

    public const WOFF = "font/woff";

    public const WOFF2 = "font/woff2";

    public const TTF = "font/ttf";

    public const EOT = "application/vnd.ms-fontobject";

    public const OTF = "font/otf";

    public const CSS = "text/css";

    public const JS = "text/javascript";

    public const CSV = "text/csv";

    public const RTF = "application/rtf";

    public const WML = "text/vnd.wap.wml";

    public const WAP = "text/vnd.wap.wmlscript";

    public const WMLC = "application/vnd.wap.wmlc";

    public const WMLS = "text/vnd.wap.wmlscript";

    public const WMLSC = "application/vnd.wap.wmlscriptc";

    public const WAP1 = "text/vnd.wap.wml";

    public const WAP2 = "application/vnd.wap.wmlc";

    public const WAP3 = "text/vnd.wap.wmlscript";

    public const WAP4 = "application/vnd.wap.wmlscriptc";

    public const WAP5 = "application/vnd.wap.xhtml+xml";

    /**
     * @var array<string, string>
     *
     * This is the list of all types of files that can be loaded
     */
    public const DOT_FILES_TO_LOAD = [
        '.css' => self::CSS,
        '.csv' => self::CSV,
        '.rtf' => self::RTF,
        '.wml' => self::WML,
        '.wap' => self::WAP,
        '.wmlc' => self::WMLC,
        '.wmls' => self::WMLS,
        '.wmlsc' => self::WMLSC,
        '.xhtml' => self::WAP5,
        '.woff' => self::WOFF,
        '.woff2' => self::WOFF2,
        '.ttf' => self::TTF,
        '.eot' => self::EOT,
        '.otf' => self::OTF,
        '.webp' => self::WEBP,
        '.bmp' => self::BMP,
        '.svg' => self::SVG,
        '.ico' => self::ICO,
        '.gif' => self::GIF,
        '.jpg' => self::JPG,
        '.jpeg' => self::JPG,
        '.png' => self::PNG,
        '.webm' => self::WEBM,
        '.ogg' => self::OGG,
        '.ogv' => self::OGV,
        '.weba' => self::WEBA,
        '.vtt' => self::WEBVTT,
        '.mp3' => self::MP3,
        '.mp4' => self::MP4,
        '.avi' => self::AVI,
        '.mov' => self::MOV,
        '.flv' => self::FLV,
        '.pdf' => self::PDF,
        '.zip' => self::ZIP,
        '.gzip' => self::GZIP,
        '.tar' => self::TAR,
        '.rar' => self::RAR,
        '.doc' => self::DOC,
        '.docx' => self::DOCX,
        '.xls' => self::XLS,
        '.xlsx' => self::XLSX,
        '.ppt' => self::PPT,
        '.pptx' => self::PPTX,
        '.xml' => self::XML,
        '.json' => self::JSON,
        '.txt' => self::TEXT
    ];

}