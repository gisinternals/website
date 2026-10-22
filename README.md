# website

Website for GISInternals.

## Runtime requirements

The public PHP endpoints support PHP 8.1 and PHP 8.4. The PHP cURL extension
(`ext-curl`) is required by `getremote.php` and the `getremote` operation in
`getcontent.php`. Remote requests are DNS-pinned with `CURLOPT_RESOLVE`; the
endpoints fail closed when cURL is unavailable.
