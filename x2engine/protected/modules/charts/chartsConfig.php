<?php

$sqlView="((
	SELECT 
	`a`.`id` AS `id`,
	`a`.`dealvalue` AS `dealValue`,
	`a`.`leadDate` AS `leadDate`,
	`a`.`createDate` AS `createDate`,
	`a`.`leadstatus` AS `leadStatus`,
	`a`.`leadSource` AS `leadSource`,
	`a`.`leadtype` AS `leadType`,
	`a`.`assignedTo` AS `assignedTo`,
	concat(`b`.`firstName`, ' ',`b`.`lastName`) AS `assignedToName`,
	`a`.`interest` AS `interest`,
	`a`.`closedate` AS `closeDate`,
	`a`.`rating` AS `confidence`,
	`a`.`visibility` AS `visibility`,
	`a`.`leadscore` AS `leadScore`,
	`a`.`dealstatus` AS `dealStatus`
	FROM (`x2_contacts` `a` JOIN `x2_users` `b`)
	WHERE ((`a`.`assignedTo` <= 0) AND (`b`.`userName` = `a`.`assignedTo`))
	)
	UNION
	(
	SELECT
	`a`.`id` AS `id`,
	`a`.`dealvalue` AS `dealValue`,
	`a`.`leadDate` AS `leadDate`,
	`a`.`createDate` AS `createDate`,
	`a`.`leadstatus` AS `leadStatus`,
	`a`.`leadSource` AS `leadSource`,
	`a`.`leadtype` AS `leadType`,
	`a`.`assignedTo` AS `assignedTo`,
	`b`.`name` AS `assignedToName`,
	`a`.`interest` AS `interest`,
	`a`.`closedate` AS `closeDate`,
	`a`.`rating` AS `confidence`,
	`a`.`visibility` AS `visibility`,
	`a`.`leadscore` AS `leadScore`,
	`a`.`dealstatus` AS `dealStatus`
	FROM (`x2_contacts` `a` JOIN `x2_groups` `b`)
	WHERE ((`a`.`assignedTo` > 0) AND (`b`.`id` = `a`.`assignedTo`))
	)
	ORDER BY leadDate ASC) x2_bi_leads";
?>
