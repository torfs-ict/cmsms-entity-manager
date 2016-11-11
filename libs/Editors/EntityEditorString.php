<?php

namespace EntityManager\Editors;

use ContentOperations;
use EntityManager;
use EntityManager\Entity;
use EntityManager\EntityEditor;
use PDO;

class EntityEditorString extends EntityEditor {
    public function AutoComplete($params) {
        $db = EntityManager::GetInstance()->MySQL();
        if (!array_key_exists('term', $_GET)) $_GET['term'] = '';
        $ret = [];
        $records = $db->query(
            "SELECT DISTINCT `p`.`content` FROM `#__content_props` AS `p` LEFT JOIN `#__content` AS `c` ON `c`.`content_id` = `p`.`content_id` WHERE `p`.`prop_name` = ? AND `c`.`type` = ? AND `p`.`content` LIKE ?",
            $this->config->name, $this->entity->Type(), sprintf('%s%%', $_GET['term'])
        )->fetchAll(PDO::FETCH_COLUMN);
        array_walk($records, function(&$item) use (&$ret) {
            if (empty($item)) return;
            $item = utf8_encode($item);
            $ret[] = ['label' => $item, 'value' => $item];
        });
        header('Content-Type: application/json');
        echo json_encode($ret);
    }

    public function CropImage($params) {
        header('Content-Type: application/json');
        /** @var Entity $entity */
        $entity = ContentOperations::get_instance()->LoadContentFromId($params['entity']);
        $entity->CropImage($params['property'], $params['index'], $_POST['x'], $_POST['y'], $_POST['width'], $_POST['height']);
    }

    public function UploadImage($params) {
        // TODO: remove old files
        header('Content-Type: text/plain');
        /** @var Entity $entity */
        $entity = ContentOperations::get_instance()->LoadContentFromId($params['entity']);
        $exif = exif_read_data($_FILES['file']['tmp_name']);
        $orientation = null;
        if (array_key_exists('COMPUTED', $exif) && array_key_exists('Orientation', $exif['COMPUTED'])) {
            $orientation = $exif['COMPUTED']['Orientation'];
        } elseif (array_key_exists('Orientation', $exif)) {
            $orientation = $exif['Orientation'];
        }
        if (!is_null($orientation)) {
            $im = imagecreatefromstring(file_get_contents($_FILES['file']['tmp_name']));
            switch($orientation) {
                case 2: $im = imageflip($im, IMG_FLIP_HORIZONTAL); break;
                case 3: $im = imageflip($im, IMG_FLIP_VERTICAL); break;
                case 4: $im = imageflip($im, IMG_FLIP_BOTH); break;
                case 5: $im = imagerotate($im, -90, 0); $im = imageflip($im, IMG_FLIP_HORIZONTAL); break;
                case 6: $im = imagerotate($im, -90, 0); break;
                case 7: $im = imagerotate($im, 90, 0); $im = imageflip($im, IMG_FLIP_HORIZONTAL); break;
                case 8: $im = imagerotate($im, 90, 0); break;
            }
            imagejpeg($im, $_FILES['file']['tmp_name']);
        }
        $entity->UploadImage($params['property'], $params['index'], $_FILES['file']['name'], $_FILES['file']['tmp_name']);
        echo cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_url'), '.entities', $entity->Id(), $params['property'], (int)$params['index'], 'original', $_FILES['file']['name']);
    }

    public function UploadFile($params) {
        // TODO: remove old files
        header('Content-Type: text/plain');
        /** @var Entity $entity */
        $entity = ContentOperations::get_instance()->LoadContentFromId($params['entity']);
        $entity->UploadFile($params['property'], $params['index'], $_FILES['file']['name'], $_FILES['file']['tmp_name']);
        echo cms_join_path(cmsms()->GetConfig()->offsetGet('uploads_url'), '.entities', $entity->Id(), $params['property'], (int)$params['index'], $_FILES['file']['name']);
    }

    public function Tags($params) {
        $db = EntityManager::GetInstance()->MySQL();
        if (!array_key_exists('term', $_GET)) $_GET['term'] = '';
        $ret = [];
        $done = [];
        $records = $db->query(
            "SELECT DISTINCT `p`.`content` FROM `#__content_props` AS `p` LEFT JOIN `#__cms_content` AS `c` ON `c`.`content_id` = `p`.`content_id` WHERE `p`.`prop_name` = ? AND `c`.`type` = ? AND `p`.`content` LIKE ?",
            $this->config->name, $this->entity->Type(), sprintf('%%%s%%', $_GET['term'])
        )->fetchAll(PDO::FETCH_COLUMN);
        array_walk($records, function(&$item) use (&$ret, &$done) {
            if (empty($item)) return;
            $item = explode(',', $item);
            foreach($item as $tag) {
                $tag = utf8_encode(trim($tag));
                if (in_array($tag, $done, true)) continue;
                if (!fnmatch($_GET['term'] . '*', $tag)) continue;
                $ret[] = ['label' => $tag, 'value' => $tag];
                $done[] = $tag;
            }
        });
        header('Content-Type: application/json');
        echo json_encode($ret);
    }}