<?php
require_once (INSTALL_PATH . FOLDER_CLASSES . 'Infos.class.php' );
require_once ('dates.php');
require_once ('url_fcts.php');

/**
 *
 */

class Dailies {

	// champs de la table dailies et dailies_summary
	const DAILIES_ID					= 'id' ;
	const DAILIES_PROJECT_ID			= 'ID_project' ;
	const DAILIES_DATE					= 'date' ;
	const DAILIES_USER					= 'user' ;
	const DAILIES_GROUP					= 'groupe' ;
	const DAILIES_TYPE					= 'type' ;
	const DAILIES_CORRESP				= 'corresp' ;
	const DAILIES_WEEK					= 'week' ;
	const DAILIES_COMMENT				= 'comment' ;

	// groupes de dailies
	const GROUP_ROOT					= 'ROOT';
	const GROUP_SHOT					= 'SHOT';
	const GROUP_SCENE					= 'SCENE';
	const GROUP_ASSET					= 'ASSET';

	// types de dailies
	const TYPE_BANK_UPLOAD				= 'BANK_UPLOAD';
	const TYPE_SHOT_BANK_UPLOAD			= 'SHOT_BANK_UPLOAD';
	const TYPE_SHOT_NEW_PUBLISHED		= 'SHOT_NEW_PUBLISHED';
	const TYPE_SHOT_MOD_PUBLISHED		= 'SHOT_MOD_PUBLISHED';
	const TYPE_SHOT_VALID_PUBLISHED		= 'SHOT_VALID_PUBLISHED';
	const TYPE_SHOT_NEW_MESSAGE			= 'SHOT_NEW_MESSAGE';
	const TYPE_ASSET_BANK_UPLOAD		= 'ASSET_BANK_UPLOAD';
	const TYPE_ASSET_NEW_PUBLISHED		= 'ASSET_NEW_PUBLISHED';
	const TYPE_ASSET_MOD_PUBLISHED		= 'ASSET_MOD_PUBLISHED';
	const TYPE_ASSET_VALID_PUBLISHED	= 'ASSET_VALID_PUBLISHED';
	const TYPE_ASSET_REVIEW_REQUEST		= 'ASSET_REVIEW_REQUEST';
	const TYPE_ASSET_REVIEW_VALID		= 'ASSET_REVIEW_VALID';
	const TYPE_ASSET_NEW_MESSAGE		= 'ASSET_NEW_MESSAGE';
	const TYPE_ASSET_HANDLED			= 'ASSET_HANDLED';
	const TYPE_SCENE_BANK_UPLOAD		= 'SCENE_BANK_UPLOAD';
	const TYPE_SCENE_NEW_PUBLISHED		= 'SCENE_NEW_PUBLISHED';
	const TYPE_SCENE_MOD_PUBLISHED		= 'SCENE_MOD_PUBLISHED';
	const TYPE_SCENE_VALID_PUBLISHED	= 'SCENE_VALID_PUBLISHED';
	const TYPE_SCENE_REVIEW_REQUEST		= 'SCENE_REVIEW_REQUEST';
	const TYPE_SCENE_REVIEW_VALID		= 'SCENE_REVIEW_VALID';
	const TYPE_SCENE_NEW_MESSAGE		= 'SCENE_NEW_MESSAGE';
	const TYPE_SCENE_HANDLED			= 'SCENE_HANDLED';
	const TYPE_FINAL_NEW_MESSAGE		= 'FINAL_NEW_MESSAGE';
	const TYPE_FINAL_NEW_UPLOAD			= 'FINAL_NEW_UPLOAD';
	const TYPE_SCENARIO_UPDATE			= 'SCENARIO_UPDATE';


	private $project_id;	// ID du projet courant
	private $project_title;	// TITRE du projet courant
	private $dailies_list ;	// Liste des dailies pour le nombre de semaine(s) donné


	public function __construct ($projID=false, $nbWeeksToLoad=4) {
		if (!$projID) { throw new Exception('Dailies::contruct() : missing project ID'); return; }
		$this->project_id = (int)$projID;
		if ($this->project_id == 0) { throw new Exception('Dailies::construct() : project ID not a number'); return; }
		$p = new Projects($this->project_id);
		$this->project_title = $p->getProjectInfos(Projects::PROJECT_TITLE);
		$olderTS	= time() - ($nbWeeksToLoad * 7 * 24 * 60 * 60);
		$olderDate	= date('Y-m-d H:i:s', $olderTS);
		$l = new Liste();
		$l->addFiltre(Dailies::DAILIES_PROJECT_ID, '=', $this->project_id, 'AND');
		$l->addFiltre(Dailies::DAILIES_DATE, '>', $olderDate, 'AND');
		$l->getListe(TABLE_DAILIES, '*', Dailies::DAILIES_DATE, 'DESC');
		$this->dailies_list = $l->simplifyList();
	}

	// Retourne les dailies formattés
	public function getDailies() {
		if ($this->dailies_list == false) return false;
		$dailies = Array();
		$prevD = false;
		foreach ($this->dailies_list as $IDdaily => $daily) {
			try {
				$dailies[$IDdaily] = Dailies::getFormatedDaily($daily, $prevD);
				$prevD = $daily;
			}
			catch(Exception $e) { continue; }
		}
		return $dailies;
	}

	// Retourne le résumé d'une semaine donnée
	public function getSummary ($week=false) {
		if ($week == false) $week = date('Y_W');
		$l = new Liste();
		$l->addFiltre(Dailies::DAILIES_PROJECT_ID, '=', $this->project_id, 'AND');
		$l->addFiltre(Dailies::DAILIES_WEEK, '=', $week, 'AND');
		$l->getListe(TABLE_DAILIES_SUMMARY, '*', Dailies::DAILIES_DATE, 'DESC');
		return $l->simplifyList();
	}


	// Formatte les données de Dailies pour un daily donné (DOIT ÊTRE UN ARRAY : $daily = Array('id'=>1, 'ID_project'=>1, etc...)
	public static function getFormatedDaily ($daily, $prevD=false) {
		$p = new Projects((int)$daily[Dailies::DAILIES_PROJECT_ID]);
		$projId = $p->getProjectInfos(Projects::PROJECT_ID_PROJECT);
		$projTitle = $p->getProjectInfos(Projects::PROJECT_TITLE);

		$saamURL = getSaamURL().'/';
		$vignette = '';
		$message  = '';
		$details = '';
		$link = Array();
		$prevInf = json_decode($prevD[Dailies::DAILIES_CORRESP], true);
		$inf	 = json_decode($daily[Dailies::DAILIES_CORRESP], true);
		$show = True;
		if (isset($prevInf['idShot']) && (@$prevInf['idShot'] == @$inf['idShot']))
			$show = False;
		if (isset($prevInf['nameAsset']) && (@$prevInf['nameAsset'] == @$inf['nameAsset']))
			$show = False;
		if (isset($prevInf['sceneID']) && (@$prevInf['sceneID'] == @$inf['sceneID']))
			$show = False;
		if (isset($prevInf['idProject']) && (@$prevInf['idProject'] == @$inf['idProject']))
			$show = False;

		switch ($daily[Dailies::DAILIES_GROUP]) {
			case Dailies::GROUP_ROOT:
				$vignette	= check_proj_vignette_ext($projId, $projTitle);
				$group		= L_ROOT;
				$groupClass = 'other';
				$icons		= '';
				$link		= Array('projID'=>$projId);
				break;
			case Dailies::GROUP_SHOT:
				$sh			= new Shots((int)$inf['idShot']);
				$shotTitle	= $sh->getShotInfos(Shots::SHOT_TITLE);
				$idSeq		= $sh->getShotInfos(Shots::SHOT_ID_SEQUENCE);
				$se			= new Sequences((int)$idSeq);
				$labelSeq	= $se->getSequenceInfos(Sequences::SEQUENCE_LABEL);
				$seqTitle	= $se->getSequenceInfos(Sequences::SEQUENCE_TITLE);
				try {$dept		= new Infos(TABLE_DEPTS);
					$dept->loadInfos('label', $inf['dept']);
					$template	= $dept->getInfo('template_name');
				} catch (Exception $e) { $template = 'dept_storyboard'; }
				$group		= strtoupper(L_SHOTS);
				$groupClass = 'shots';
				$myShots	= (@$_SESSION['user']) ? $_SESSION['user']->getUserShots() : Array();
				$icons		= (in_array($inf['idShot'], $myShots)) ?  '<div class="inline mid" title="your shot"><span class="ui-icon ui-icon-person"></span></div>' : '';
				$vignette	= check_shot_vignette_ext($projId, $labelSeq, $sh->getShotInfos(Shots::SHOT_LABEL));
				$link		= Array('projID'=>$projId, 'seqID'=>(int)$idSeq, 'shotID'=>(int)$inf['idShot'], 'dept'=>$inf['dept'], 'template'=>$template);
				break;
			case Dailies::GROUP_ASSET:
				$asset		= new Assets($projId, (string)$inf['nameAsset']);
				$assetID	= $asset->getIDasset();
				$group		= strtoupper(L_ASSETS);
				$groupClass = 'assets';
				$myAssets	= (@$_SESSION['user']) ? $_SESSION['user']->getUserAssets() : Array();
				$icons		= (in_array($assetID, $myAssets)) ?  '<div class="inline mid" title="your asset"><span class="ui-icon ui-icon-person"></span></div>' : '';
				$vignette	= check_asset_vignette($inf['pathAsset'], $inf['nameAsset'], $projId);
				$link		= Array('projID'=>$projId, 'assetID'=>$assetID, 'pathAsset'=>$inf['pathAsset'], 'nameAsset'=>$inf['nameAsset']);
				break;
			case Dailies::GROUP_SCENE:
				$scene		= new Scenes((int)$inf['sceneID']);
				$sceneTitle = $scene->getSceneInfos(Scenes::TITLE);
				$group		= mb_convert_case(L_SCENES, MB_CASE_UPPER);
				$groupClass = 'scenes';
				$myScenes	= (@$_SESSION['user']) ? $_SESSION['user']->getUserAssets() : Array();
				$icons		= (in_array($inf['sceneID'], $myScenes)) ?  '<div class="inline mid" title="your scene"><span class="ui-icon ui-icon-person"></span></div>' : '';
				$vignette	= check_scene_vignette($inf['sceneID']);
				$link		= Array('projID'=>$projId, 'sceneID'=>$inf['sceneID']);
				break;
			default:
				$message  = 'Daily type unknown... ';
				break;
		}
		$vignette = $saamURL.$vignette;
		$link['saamURL'] = $saamURL;

		$labelDept = '<i>(???)</i>';
		if (isset($inf['deptID'])) {
			$d = new Infos(TABLE_DEPTS);
			$d->loadInfos('id', $inf['deptID']);
			$labelDept = '<b>'.strtoupper($d->getInfo('label')).'</b>';
		}
		if (isset($inf['folder']))
			$inf['folder'] = ($inf['folder'] == "stills") ? 'WIP' : $inf['folder'];

		switch ($daily[Dailies::DAILIES_TYPE]) {
			case Dailies::TYPE_BANK_UPLOAD:
				$message .= $inf['nbF'].' <b>new file'.(((int)$inf['nbF'] > 1) ? 's' : '').'</b> in project\'s BANK, folder <b>'.@$inf['folder'].'</b>';
				$group = 'BANK';
				break;
			case Dailies::TYPE_SHOT_BANK_UPLOAD:
				$message .= $inf['nbF'] . ' <b>new file'. (($inf['nbF'] > 1) ? 's' : '').'</b> into folder <b>'.strtoupper($inf['folder'])
								.'</b> of "'.$seqTitle.' - '.$shotTitle.'", in department <b>'.strtoupper($inf['dept']).'</b>';
				break;
			case Dailies::TYPE_SHOT_NEW_PUBLISHED:
				$message .= 'New published for <b>"'.$seqTitle.' - '.$shotTitle.'"</b>, in department <b>'.strtoupper($inf['dept']).'</b>';
				$icons	  = '<div class="inline mid ui-state-error-text" title="important"><span class="ui-icon ui-icon-alert"></span></div> '.$icons;
				break;
			case Dailies::TYPE_SHOT_MOD_PUBLISHED:
				$message .= 'Published was modified for <b>"'.$seqTitle.' - '.$shotTitle.'"</b>, in department <b>'.strtoupper($inf['dept']).'</b>';
				$icons	  = '<div class="inline mid ui-state-error-text" title="important"><span class="ui-icon ui-icon-alert"></span></div> '.$icons;
				break;
			case Dailies::TYPE_SHOT_VALID_PUBLISHED:
				$message .='Published VALIDATION, in <b>"'.$seqTitle.' - '.$shotTitle.'"</b>, in department <b>'.strtoupper($inf['dept']).'</b>';
				break;
			case Dailies::TYPE_SHOT_NEW_MESSAGE:
				$message .= 'New message for <b>"'.$seqTitle.' - '.$shotTitle.'"</b>, in department <b>'.strtoupper($inf['dept']).'</b>';
				$details  = nl2br(urldecode($inf['txtMess']));
				$icons	  = '<div class="inline mid ui-state-highlight ui-corner-all" title="'.preg_replace('/"/','',urldecode($inf['txtMess'])).'">
								<span class="ui-icon ui-icon-mail-closed"></span></div> '.$icons;
				break;
			case Dailies::TYPE_ASSET_BANK_UPLOAD:
				$message .= $inf['nbF'] . ' new file'. (($inf['nbF'] > 1) ? 's' : '').' into folder <b>'.strtoupper($inf['folder'])
								.'</b> of <b>"'.$inf['pathAsset'].$inf['nameAsset'].'"</b>, in department '.$labelDept;
				break;
			case Dailies::TYPE_ASSET_NEW_PUBLISHED:
				$message .= 'New published for <b>"'.$inf['pathAsset'].$inf['nameAsset'].'"</b>, in department '.$labelDept;
				$icons	  = '<div class="inline mid ui-state-error-text" title="important"><span class="ui-icon ui-icon-alert"></span></div> '.$icons;
				break;
			case Dailies::TYPE_ASSET_MOD_PUBLISHED:
				$message .= 'Published was modified for <b>"'.$inf['pathAsset'].$inf['nameAsset'].'"</b>, in department '.$labelDept;
				$icons	  = '<div class="inline mid ui-state-error-text" title="important"><span class="ui-icon ui-icon-alert"></span></div> '.$icons;
				break;
			case Dailies::TYPE_ASSET_VALID_PUBLISHED:
				$message .= 'Published VALIDATION, in <b>"'.$inf['pathAsset'].$inf['nameAsset'].'"</b>, in department '.$labelDept;
				break;
			case Dailies::TYPE_ASSET_REVIEW_REQUEST:
				$message .='Review <b>request</b>, for <b>"'.$inf['pathAsset'].$inf['nameAsset'].'"</b>';
				$icons	  = '<div class="inline mid ui-state-error ui-corner-all" title="'.preg_replace('/"/','',urldecode($inf['comment'])).'">
								<span class="ui-icon ui-icon-alert"></span></div> '.$icons;
				break;
			case Dailies::TYPE_ASSET_REVIEW_VALID:
				$message .='Review <b class="colorOk">validated</b>, for <b>"'.$inf['pathAsset'].$inf['nameAsset'].'"</b>';
				break;
			case Dailies::TYPE_ASSET_NEW_MESSAGE:
				$message .= 'New message for <b>"'.$inf['pathAsset'].$inf['nameAsset'].'"</b>, in department '.$labelDept;
				$details  = nl2br(urldecode($inf['txtMess']));
				$icons	  = '<div class="inline mid ui-state-highlight ui-corner-all" title="'.preg_replace('/"/','',urldecode($inf['txtMess'])).'">
								<span class="ui-icon ui-icon-mail-closed"></span></div> '.$icons;
				break;
			case Dailies::TYPE_ASSET_HANDLED:
				$message .= '<b>"'.$inf['pathAsset'].$inf['nameAsset'].'"</b> ';
				if ((int)$inf['idNewHandler'] == 0) {
					$message .= 'is now <b>FREE</b> !';
					$icons	  = '<div class="inline mid ui-state-focus" title="asset free!"><span class="ui-icon ui-icon-unlocked"></span></div> '.$icons;
				}
				else {
					$h = new Users((int)$inf['idNewHandler']);
					$message .= 'handled by <b>'.$h->getUserInfos(Users::USERS_PSEUDO).'</b>.<br />The asset is now <b>LOCKED</b>.';
					$icons	  = '<div class="inline mid ui-state-error" title="asset locked!"><span class="ui-icon ui-icon-locked"></span></div> '.$icons;
				}
				break;
			case Dailies::TYPE_SCENE_BANK_UPLOAD:
				$message .= $inf['nbF'] . ' <b>new file'. (($inf['nbF'] > 1) ? 's' : '').'</b> into folder <b>'.strtoupper($inf['folder'])
								.'</b> of "'.$sceneTitle.'", in department '.$labelDept;
				break;
			case Dailies::TYPE_SCENE_NEW_PUBLISHED:
				$message .= 'New published for <b>"'.$sceneTitle.'"</b>, in department '.$labelDept;
				$icons	  = '<div class="inline mid ui-state-error-text" title="important"><span class="ui-icon ui-icon-alert"></span></div> '.$icons;
				break;
			case Dailies::TYPE_SCENE_MOD_PUBLISHED:
				$message .= 'Published was modified for <b>"'.$sceneTitle.'"</b>, in department '.$labelDept;
				$icons	  = '<div class="inline mid ui-state-error-text" title="important"><span class="ui-icon ui-icon-alert"></span></div> '.$icons;
				break;
			case Dailies::TYPE_SCENE_VALID_PUBLISHED:
				$message .= 'Published VALIDATION, in <b>"'.$sceneTitle.'"</b>, in department '.$labelDept;
				break;
			case Dailies::TYPE_SCENE_REVIEW_REQUEST:
				$message .='Review <b>request</b>, for <b>"'.$sceneTitle.'"</b>';
				$icons	  = '<div class="inline mid ui-state-error ui-corner-all" title="'.preg_replace('/"/','',urldecode($inf['comment'])).'">
								<span class="ui-icon ui-icon-alert"></span></div> '.$icons;
				break;
			case Dailies::TYPE_SCENE_REVIEW_VALID:
				$message .='Review <b class="colorOk">validated</b>, for <b>"'.$sceneTitle.'"</b>';
				break;
			case Dailies::TYPE_SCENE_NEW_MESSAGE:
				$message .= 'New message for <b>"'.$sceneTitle.'"</b>, in department '.$labelDept;
				$details  = nl2br(urldecode($inf['txtMess']));
				$icons	  = '<div class="inline mid ui-state-highlight ui-corner-all" title="'.preg_replace('/"/','',urldecode($inf['txtMess'])).'">
								<span class="ui-icon ui-icon-mail-closed"></span></div> '.$icons;
				break;
			case Dailies::TYPE_SCENE_HANDLED:
				$message .= '<b>"'.$sceneTitle.'"</b> ';
				if ((int)$inf['idNewHandler'] == 0) {
					$message .= 'is now <b>FREE</b> !';
					$icons	  = '<div class="inline mid ui-state-focus" title="scene free!"><span class="ui-icon ui-icon-unlocked"></span></div> '.$icons;
				}
				else {
					$h = new Users((int)$inf['idNewHandler']);
					$message .= 'handled by <b>'.$h->getUserInfos(Users::USERS_PSEUDO).'</b>.<br />The scene is now <b>LOCKED</b>.';
					$icons	  = '<div class="inline mid ui-state-error" title="scene locked!"><span class="ui-icon ui-icon-locked"></span></div> '.$icons;
				}
				break;
			case Dailies::TYPE_FINAL_NEW_MESSAGE:
				$message .= 'New <b>message</b> in department FINAL';
				$group = 'FINAL';
				break;
			case Dailies::TYPE_FINAL_NEW_UPLOAD:
				$message .= 'New <b>upload</b> in department FINAL';
				$group = 'FINAL';
				break;
			case Dailies::TYPE_SCENARIO_UPDATE:
				$message .= 'Scenario updated.';
				$group = 'SCENARIO';
				break;
		}
		$u = new Users((int)$daily[Dailies::DAILIES_USER]);
		$uPseudo = $u->getUserInfos(Users::USERS_PSEUDO);
		unset($u);

		return Array(
			'show'		=> $show,
			'project'	=> $projTitle,
			'date'		=> SQLdateConvert($daily[Dailies::DAILIES_DATE], 'format', DATE_FORMAT.' - H:i'),
			'time'		=> strtotime($daily[Dailies::DAILIES_DATE]),
			'user'		=> $uPseudo,
			'groupClass'=> $groupClass,
			'group'		=> $group,
			'vignette'	=> $vignette,
			'message'	=> $message,
			'details'	=> $details,
			'link'		=> $link,
			'icons'		=> $icons
		);
	}


	/**************************************************************************/

	// Pour ajouter une entrée dans la table dailies de la BDD
	public static function add_dailies_entry($projID, $group, $type, $corresp) {
		if ((int)$projID == 0) {throw new Exception('add_dailies_entry : $projID is not an integer'); return false;}
		$i = new Infos(TABLE_DAILIES);
		$i->addInfo(Dailies::DAILIES_PROJECT_ID, $projID);
		$i->addInfo(Dailies::DAILIES_USER, $_SESSION['user']->getUserInfos(Users::USERS_ID));
		$i->addInfo(Dailies::DAILIES_GROUP, $group);
		$i->addInfo(Dailies::DAILIES_TYPE, $type);
		$i->addInfo(Dailies::DAILIES_CORRESP, $corresp);
		$i->save();
		Dailies::send_alert_mails($projID, $i->getInfo());
	}

	// Pour ajouter une entrée dans la table dailies_summary de la BDD
	public static function add_dailies_summary($projID, $comment) {
		if ((int)$projID == 0) {throw new Exception('add_dailies_summary : $projID is not an integer'); return false;}
		$i = new Infos(TABLE_DAILIES_SUMMARY);
		$i->addInfo(Dailies::DAILIES_PROJECT_ID, $projID);
		$i->addInfo(Dailies::DAILIES_USER, $_SESSION['user']->getUserInfos(Users::USERS_ID));
		$i->addInfo(Dailies::DAILIES_WEEK, date('Y_W'));
		$i->addInfo(Dailies::DAILIES_COMMENT, stripslashes($comment));
		$i->save();
	}

	// Pour ajouter une entrée dans la table dailies_summary de la BDD
	public static function mod_dailies_summary($projID, $commID, $comment) {
		if ((int)$projID == 0) {throw new Exception('add_dailies_summary : $projID is not an integer'); return false;}
		$i = new Infos(TABLE_DAILIES_SUMMARY);
		$i->loadInfos(Dailies::DAILIES_ID, $commID);
		$i->addInfo(Dailies::DAILIES_COMMENT, stripslashes($comment));
		$i->save();
	}

	// Pour supprimer un commentaire de summary
	public static function delete_dailies_comment ($idComment) {
		if ((int)$idComment == 0) {throw new Exception('delete_dailies_comment : $idComment is not an integer'); return false;}
		$i = new Infos(TABLE_DAILIES_SUMMARY);
		$i->loadInfos(Dailies::DAILIES_ID, (int)$idComment);
		$idUserComm = $i->getInfo(Dailies::DAILIES_USER);
		if (!($_SESSION['user']->isSupervisor() || $_SESSION['user']->isDemo())) {
			if ((int)$idUserComm != (int)$_SESSION['user']->getUserInfos(Users::USERS_ID)) {
				throw new Exception('Permission denied.');
			}
		}
		$i->delete();
	}

	// Pour supprimer toutes les dailies d'un projet
	public static function delete_all_dailies_project($idProj) {
		$l = new Liste();
		$dlList = $l->getListe(TABLE_DAILIES, 'id', 'id', 'ASC', Dailies::DAILIES_PROJECT_ID, '=', $idProj);
		if (is_array($dlList)) {
			foreach($dlList as $dlId) {
				$i = new Infos(TABLE_DAILIES);
				$i->loadInfos(Dailies::DAILIES_ID, $dlId);
				$i->delete();
			}
		}
		$dlsList = $l->getListe(TABLE_DAILIES_SUMMARY, 'id', 'id', 'ASC', Dailies::DAILIES_PROJECT_ID, '=', $idProj);
		if (is_array($dlsList)) {
			foreach($dlsList as $dlsId) {
				$i = new Infos(TABLE_DAILIES_SUMMARY);
				$i->loadInfos(Dailies::DAILIES_ID, $dlsId);
				$i->delete();
			}
		}
	}

	////////////////////////////////////////////////////////////////////////////

	// Envoi des mails d'alerte temps réel
	public static function send_alert_mails ($projID, $dailyInfos) {
		$local = '';
		if (IS_LOCAL === true)
			$local = ' (local)';
		if (DONT_SENDMAIL_LOCAL === true && IS_LOCAL === true)
			return;

		if (!ALERT_MESSAGES && !ALERT_UPLOADS && !ALERT_RETAKES)
			return;
		if (preg_match('/MESSAGE$/', $dailyInfos[Dailies::DAILIES_TYPE]) && !ALERT_MESSAGES)
			return;
		if (preg_match('/UPLOAD/', $dailyInfos[Dailies::DAILIES_TYPE]) && !ALERT_UPLOADS)
			return;
		if (preg_match('/PUBLISHED$/', $dailyInfos[Dailies::DAILIES_TYPE]) && !ALERT_RETAKES)
			return;

		$hostNameArr = explode('.', $_SERVER['HTTP_HOST']);
		$whichSaAM = strtoupper($hostNameArr[0]);

		$dailyInfos[Dailies::DAILIES_DATE] = date('Y-m-d H:i:s');
		$dailyFormated = Dailies::getFormatedDaily($dailyInfos);

		$subject = 'SaAM INFO: '. preg_replace('#<b>|</b>|<br />#', '', $dailyFormated['message']);

		$body = '
		<body style="background-color: #333; color: #ccc; padding: 5px;">
			<h4><a style="color:#006E9E; text-decoration:none; outline: none;" href="'.getSaamURL().'">SaAM '.$whichSaAM.'</a> | '. strtoupper($dailyFormated['project']). ' | ' .$dailyFormated['group'] .'</h4>
			<div style="background-color: #555; margin: 5px 0px;">
				<div style="float: left; margin: 5px 10px 5px 5px;">
					<img src="'. preg_replace('/ /', '%20',$dailyFormated['vignette']) .'" height="75" width="133" />
				</div>
				<div style="padding: 3px;">
					<p style="background-color: #333; margin: 2px 0px; padding: 2px 0px;">
						<span style="color: #999;">'. $dailyFormated['date'] .' | by</span> '. $dailyFormated['user'] .'
					</p>
					<p style="margin: 3px 0px;">'. $dailyFormated['message'] .'</p>
					<p style="background-color:#4b4b4b; color:#fff; padding: 2px 5px; margin: 3px 0px;">'. $dailyFormated['details'] .'</p>
				</div>
				<div style="clear: both;"></div>
			</div>
			<div style="font-size: 0.8em; color: #666;">SaAM instant information message: you can disable those notifications in your SaAM\'s User Preferences.</div>
		</body>';

		$mails = Array();
		$onlySupervisors = (preg_match('/_REVIEW_/', $dailyInfos[Dailies::DAILIES_TYPE])) ? true : false;



		$inf = json_decode($dailyInfos[Dailies::DAILIES_CORRESP], true);

		if ($dailyInfos[Dailies::DAILIES_GROUP] == Dailies::GROUP_SHOT) {
			$shotID = $inf['idShot'];
			$mails = Users::getUsersMails($projID, 'shot', $shotID, $onlySupervisors, true);
		}
		elseif ($dailyInfos[Dailies::DAILIES_GROUP] == Dailies::GROUP_SCENE) {
			$sceneID = $inf['sceneID'];
			$mails = Users::getUsersMails($projID, 'scene', $sceneID, $onlySupervisors, true);
		}
		elseif ($dailyInfos[Dailies::DAILIES_GROUP] == Dailies::GROUP_ASSET) {
			$assetID = $inf['idAsset'];
			$mails = Users::getUsersMails($projID, 'asset', $assetID, $onlySupervisors, true);
		}
		else
			$mails = Users::getUsersMails($projID, false, false, false, true);
		$to = implode(', ', $mails);

		$headers  = "MIME-Version: 1.0\r\n";
		$headers .= "From: ".SAAM_MAILBOT."\r\n";
		$headers .= "Content-type: text/html; charset=utf-8";

		$nbMailSent = 0;
		foreach($mails as $toSolo) {
			if (mail($toSolo, $subject, $body, $headers))
				$nbMailSent += 1;
		}

		file_put_contents(INSTALL_PATH.'temp/lastMailSent.html',"<html>\n"
			."<head><meta charset='utf-8' /></head>\n"
			."$subject<br /><br />\n"
			."$body<br /><br />\n"
			."$to<br /><br />\n"
			."DONE! $nbMailSent emails sent.<br />\n"
		."</html>");

	}

}

?>
