<?php
namespace Lib\Http;

use Lib\Config;
use Lib\NameBuilder;

class Handler implements IHandler
{
    public function onRequest(Context $context, Config $config)
    {
        $this->notFound($context);
    }
    protected function notFound(Context $context)
    {
        $context->responseStatus(404);
        $context->responseHeader('Content-Type', 'text/plain');
        $context->responseBody('Not Found');
        $context->responseFinish();
    }
    protected function redirect(Context $context, string $url)
    {
        $context->responseStatus(301);
        $context->responseHeader('Location', $url);
        $context->responseHeader('Content-Type', 'text/html');
        $htmlUrl = htmlspecialchars($url);
        $html = <<<HTML
<html>
<head>
<meta http-equiv="refresh" content="5;url={$htmlUrl}">
<script language="javascript" type="text/javascript">
location.href='{$htmlUrl}';
</script>
</head>
</html>
HTML;
        $context->responseBody($html);
        $context->responseFinish();
    }
    protected function responseText(Context $context, string $text)
    {
        $context->responseStatus(200);
        $context->responseHeader('Content-Type', 'text/plain');
        $context->responseBody($text);
        $context->responseFinish();
    }
    protected function responseJson(Context $context, $data)
    {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $context->responseStatus(200);
        $context->responseHeader('Content-Type', 'application/json');
        $context->responseBody($json);
        $context->responseFinish();
    }

}