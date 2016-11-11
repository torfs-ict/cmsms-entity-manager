<?php

use EntityManager\Entity;

/** @var EntityManager $this */
if (!isset($gCms)) exit;

/** @var Entity $content */
if ((int)$params['entity'] < 1) {
    $content = ContentOperations::get_instance()->CreateNewContent($params['type']);
} else {
    $content = ContentOperations::get_instance()->LoadContentFromId($params['entity']);
}
$editor = $content->GetEditor($params['property']);

call_user_func_array([$editor, $params['method']], [$params]);