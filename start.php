<?php

/**
 * @param array $breadcrumbs
 *
 * @return void
 */
function ufcoe_crumbs_alter_breadcrumbs(&$breadcrumbs) {
	// normalize keys, just in case
	$breadcrumbs = array_values($breadcrumbs);

	// remove trailing non-link crumbs (the title is already in the current page)
	for ($i = count($breadcrumbs) - 1; $i >= 0; --$i) {
		if (!$breadcrumbs[$i]['link']) {
			unset($breadcrumbs[$i]);
		} else {
			break;
		}
	}

	// User > Files instead of Files > User
	// Groups > My Group > Files instead of Files > My Group
	$page_owner = elgg_get_page_owner_entity();
	if (!$page_owner) {
		return;
	}

	$page_owner_url = $page_owner->getURL();
	if ($page_owner_url === preg_replace('~\\?.*~', '', current_page_url())) {
		// this is the profile page
		return;
	}

	// find the first ".../all" crumb
	$all_index = -1;
	$all_title = '';
	foreach ($breadcrumbs as $i => $crumb) {
		if (preg_match('~/all$~', $crumb['link'])) {
			$all_index = $i;
			$all_title = $crumb['title'];
			break;
		}
	}

	if ($all_index > -1) {
		$breadcrumbs[$all_index] = array(
			'link' => $page_owner_url,
			'title' => $page_owner->name,
		);

		// insert the all Groups link before the group
		if ($page_owner instanceof ElggGroup) {
			array_splice($breadcrumbs, $all_index, 0, array(
				array(
					'link' => 'groups/all',
					'title' => elgg_echo('groups'),
				),
			));
			$all_index += 1;
		}

		if (isset($breadcrumbs[$all_index + 1]) && $breadcrumbs[$all_index + 1]['title'] === $page_owner->name) {
			if ($breadcrumbs[$all_index + 1]['link'] == $page_owner_url) {
				// duplicate
				array_splice($breadcrumbs, $all_index + 1, 1);
			} else {
				$breadcrumbs[$all_index + 1]['title'] = $all_title;
			}
		}
	}
}
