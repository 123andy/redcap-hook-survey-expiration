<?php

/**
	This is a PROJECT HOOKS Master File.
	
	It is included in EVERY hook event for the specified project.
	You can use a variable called $hook_event to determine whether or not to take action on the call
	
	This file should be located in server/pidxxx/custom_hooks.php
	
	For example:
	
	if ($hook_event == 'redcap_add_edit_records_page') {
		print "<div class='yellow'>A custom hook has been triggered for $hook_event in project $project_id.</div>";
	}
	
	You can use the same code for multiple events, such as:
	
	if ($hook_event == 'redcap_data_entry_form' || $hook_event == 'redcap_survey_page') {
		print "<div class='yellow'>Your entering data on project $project_id.</div>";
	}
	
	
	If you use this custom_hooks.php file you can delete all of the function_name.php files from the template directory as they aren't necessary
	
**/


if ($hook_event == 'redcap_survey_page_top') {
	
	// This is a survey-page top hook that is intended to 'expire' a survey after 24 hours from the date 'bi_initial_ts';
	
	// Variables in scope are: int $project_id, string $record = NULL, string $instrument, int $event_id, int $group_id = NULL, string $survey_hash, int $response_id = NULL )
	
	// IN THIS CASE, I HAD THE DATE AS A VARIABLE, SO I AM USING REDCap::getData to obtain in.  In other cases, you will want to pull
	// the actual date the email was sent from the redcap_surveys_scheduler_queue table.  If you have to implement this before me, please
	// post your code back to this hook example for others to use...
	
	if ($instrument == 'daily_behavioral_questions') {
		// Set the number of hours before which this survey should 'expire'
		$max_age_hours = 24;
		
		// Get Start Date/Time
		$q = REDCap::getData('json',$record, array('bi_initial_ts','bi_day_number'));
		$records = json_decode($q,true);
		$bi_initial_ts = isset($records[0]['bi_initial_ts']) ? $records[0]['bi_initial_ts'] : false;
		
		
		if ($bi_initial_ts) {
			$ts = strtotime($bi_initial_ts);
			$delta_hours = floor((strtotime('NOW') - strtotime($bi_initial_ts)) / 3600);
			if ($delta_hours > $max_age_hours) {
				REDCap::logEvent("Survey Access Rejected", "$delta_hours have passed", NULL, $record);
				$msg =  "<B>This survey has expired</b><br><br>Per study design, all surveys must be submitted within $max_age_hours hours of the initial invitation.<br><br><div style='font-size:smaller';><i>It has been $delta_hours hours for this survey link</i></div>";
				displayMsg($msg, "actionMsg", "center", "red", "exclamation_red.png", 1000);
				exit();
			}
		}
	}
	
}//redcap_survey_page_top
