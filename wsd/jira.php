<?php

require_once("lib/curl.php");

class jira {
	function __construct($conf) {
		include($conf);
		#
		# Important note : 
		# $jira_auth = base64_encode("$jirauser:$jirapass");
		#
		$this->c    = new curl($jira_url, ["Authorization: Basic $jira_auth", "Content-Type: application/json" ], /*true); #*/ /* debug */ false);
	}

	function issues($type = "", $req = "", $expand = "") {
		$n   = 0; 
		$max = 1000;
		$jql = "";
		$p = new StdClass();
		#if (count($fields) > 0) {
			#$p->fields = $fields;
		#}
		if ($expand != "") {
			$p->expand = explode(',', $expand);
		}
		if ($type != '') {
			$p->jql = "issuetype='$type'";
			
		}
		if ($req != '') {
			$p->jql .= " and $req";
		}

		$issues=[];	
		while ($n < $max) {
			$p->startAt = $n;
			$r = $this->c->post("search", json_encode($p));
			if ($r == null) {
				print("post returned null\n");
				return null;
			}
			$rr = json_decode($r);
			if ($rr == null || $rr === false) {
				print("json decode returned null or false\n");
				return null;
			}
			if (property_exists($rr, "errorMessages")) {
				foreach ($rr->errorMessages as $msg) print("Error: $msg\n");
				return null;
			}
			$max = $rr->total;
			$issues = array_merge($issues, $rr->issues);
			$n += $rr->maxResults;
		}		
		#print(json_encode($issues));
		return $issues;
	}
}
?>
