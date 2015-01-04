<?php
$SQL_FROM = $wpdb->users;
$SQL_WHERE = 'display_name';
$searchq = stripslashes($_GET['q']);
$search = '%'.$searchq.'%';
$getRecord_sql = $wpdb->prepare("SELECT ID,user_login,display_name FROM {$SQL_FROM} WHERE {$SQL_WHERE} LIKE %s LIMIT 5",$search);
$rows = $wpdb->get_results($getRecord_sql);
if(strlen($searchq)>0)
{
	echo "<ul>";
	if ($wpdb->num_rows)
	{
		foreach($rows as $row)
		{
			if($row->ID != $user_ID) //Don't let users message themselves
			{
				
				?>
				<li><a href="#" onClick="fepfillText('<?php echo $row->user_login; ?>','<?php echo $row->display_name; ?>');return false;"><?php echo $row->display_name; ?></a></li>
				<?php
			
			}
		}
	}
	else
		echo "<li>".__("No Matches Found", "fep")."</li>";
	echo "</ul>";
}
die();
?>