<?php

namespace Plugin\Poker;

class Controller extends \Controller {

	public function index(\Base $f3) {
		$user_id = $this->_requireLogin();
		$filter = "";
		if($f3->get("GET.group_id")) {
			$group = new \Model\User\Group();
			$users = $group->find(array("group_id = ?", $params["groupid"]));
			foreach($users as $u) {
				$filter_users[] = $u["user_id"];
			}
			$filter = "AND owner_id IN (" . implode(",", $filter_users) . ")";
		}

		$issue = new \Model\Issue\Detail();

		$large_projects = $f3->get("db.instance")->exec("SELECT DISTINCT parent_id FROM issue WHERE parent_id IS NOT NULL AND type_id = ?", $f3->get("issue_type.project"));
		$large_project_ids = array();
		foreach($large_projects as $p) {
			$large_project_ids[] = $p["parent_id"];
		}

		// Load backlog
		if($large_project_ids) {
			$large_project_ids = implode(",", $large_project_ids);
			$projects = $issue->find(
				array("deleted_date IS NULL AND sprint_id IS NULL AND type_id = ? AND status_closed = '0' AND id NOT IN ({$large_project_ids}) $filter", $f3->get("issue_type.project")),
				array('order' => 'priority DESC, due_date')
			);
		} else {
			$projects = $issue->find(
				array("deleted_date IS NULL AND sprint_id IS NULL AND type_id = ? AND status_closed = '0' $filter", $f3->get("issue_type.project")),
				array('order' => 'priority DESC, due_date')
			);
		}
		$f3->set("projects", $projects);

		// Load existing votes
		$vote = new Model\Vote;
		$project_ids = array();
		foreach($projects as $p) {
			$project_ids[] = $p->id;
		}
		$votes = $vote->find(array("user_id = ? AND project_id IN (" . implode(",", $project_ids) . ")", $user_id));
		$vote_array = array();
		foreach($votes as $v) {
			$vote_array[] = array(
				"project_id" => $v->project_id,
				"vote" => $v->vote
			);
		}
		$f3->set("votes", $vote_array);

		$f3->set("title", "Backlog Poker");
		$this->_render("poker/view/index.html");
	}

	public function results(\Base $f3) {
		$user_id = $this->_requireLogin();
		$filter = "";
		if($f3->get("GET.group_id")) {
			$group = new \Model\User\Group();
			$users = $group->find(array("group_id = ?", $params["groupid"]));
			foreach($users as $u) {
				$filter_users[] = $u["user_id"];
			}
			$filter = "AND owner_id IN (" . implode(",", $filter_users) . ")";
		}

		$issue = new \Model\Issue\Detail();

		$large_projects = $f3->get("db.instance")->exec("SELECT DISTINCT parent_id FROM issue WHERE parent_id IS NOT NULL AND type_id = ?", $f3->get("issue_type.project"));
		$large_project_ids = array();
		foreach($large_projects as $p) {
			$large_project_ids[] = $p["parent_id"];
		}

		// Load backlog
		if($large_project_ids) {
			$large_project_ids = implode(",", $large_project_ids);
			$projects = $issue->find(
				array("deleted_date IS NULL AND sprint_id IS NULL AND type_id = ? AND status_closed = '0' AND id NOT IN ({$large_project_ids}) $filter", $f3->get("issue_type.project")),
				array('order' => 'priority DESC, due_date')
			);
		} else {
			$projects = $issue->find(
				array("deleted_date IS NULL AND sprint_id IS NULL AND type_id = ? AND status_closed = '0' $filter", $f3->get("issue_type.project")),
				array('order' => 'priority DESC, due_date')
			);
		}
		$f3->set("projects", $projects);

		// Load existing votes
		$vote = new Model\Vote;
		$project_ids = array();
		foreach($projects as $p) {
			$project_ids[] = $p->id;
		}
		$vote->user_name = "SELECT name FROM user WHERE user.id = poker_vote.user_id";
		$votes = $vote->find(array("project_id IN (" . implode(",", $project_ids) . ")", $user_id));
		$vote_array = array();
		foreach($votes as $v) {
			$vote_array[$v->project_id][] = array(
				"user_name" => $v->user_name,
				"vote" => $v->vote
			);
		}
		$f3->set("votes", $vote_array);

		$f3->set("title", "Backlog Poker Results");
		$this->_render("poker/view/results.html");
	}

	public function post(\Base $f3) {
		$user_id = $this->_requireLogin();
		$vote = new Model\Vote;
		$vote->load(array("user_id = ? AND project_id = ?", $user_id, $f3->get("POST.project_id")));
		$vote->user_id = $user_id;
		$vote->project_id = $f3->get("POST.project_id");
		$vote->vote = $f3->get("POST.vote");
		$vote->save();
		$this->_printJson($vote->cast());
	}

}
