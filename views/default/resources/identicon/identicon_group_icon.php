<?php
/**
 * Identicon Group Icon display
 *
 */

$group_guid = elgg_extract('group_guid', $vars);

/** @var ElggGroup $group */
$group = get_entity($group_guid);
if (!($group instanceof ElggGroup)) {
	header("HTTP/1.1 404 Not Found");
	exit;
}

// If is the same ETag, content didn't changed.
$etag = $group->icontime . $group_guid;
if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag) {
	header("HTTP/1.1 304 Not Modified");
	exit;
}

$size = strtolower(elgg_extract('size', $vars));
if (!in_array($size, ['large', 'medium', 'small', 'tiny', 'master'])) {
	$size = "medium";
}

$seed = identicon_seed($group);

$filehandler = new ElggFile();
$filehandler->owner_guid = $group->owner_guid;
$filehandler->setFilename("identicon/" . $seed . '/' . $size . ".jpg");

$success = false;
if ($filehandler->open("read")) {
	if ($contents = $filehandler->read($filehandler->getSize())) {
		$success = true;
	}
}

if (!$success) {
	$contents = elgg_view("groups/default{$size}.gif");
	header("Content-type: image/gif");
} else {
	header("Content-type: image/jpeg");
}

header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', strtotime("+10 days")), true);
header("Pragma: public");
header("Cache-Control: public");
header("Content-Length: " . strlen($contents));
header("ETag: \"$etag\"");
echo $contents;
