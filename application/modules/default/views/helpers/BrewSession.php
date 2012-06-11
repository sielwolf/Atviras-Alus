<?

class Zend_View_Helper_BrewSession extends Zend_View_Helper_Abstract {

	function brewSession() {
		return $this;
	}

	function editableRow($session = array(), $add = true) {
		$fields = array("session_name" => "Virimo pavadinimas", "user_name" => "Aludaris", "recipe_name" => "Receptas", "session_size" => "Alaus kiekis (l.)", "session_og" => "OG", "session_fg" => "FG", "session_primarydate" => "Pirminė fermentacija", "session_secondarydate" => "Antrinė fermentacija", "session_caskingdate" => "Išpilstyta", "session_comments" => "Pastabos");
		if (!isset($session["redirect"])) {
			$session["redirect"] = "brew-session/brewer";
		}
		if ($add) {
			$session = array_merge($session, array("session_name" => "", "session_size" => "", "session_og" => "", "session_fg" => "", "session_comments" => "", "session_caskingdate" => "", "session_secondarydate" => "", "session_primarydate" => ""));
			$line2 = '
				<dl>
					<dt>&nbsp;</dt>
					<dd>
						<input type="hidden" name="redirect" value="' . $session['redirect'] . '" />
						<input type="hidden" name="session_recipe" value="' . $session['recipe_id'] . '" />
						<input type="hidden" name="session_brewer" value="' . $session['user_id'] . '" />
						<input class="ui-button" type="submit" value="Pridėti" />
					</dd>
					<div class="clear"></div>
				</dl>';
			$action = "/brew-session/add";
		} else {
			$line2 = '
				<dl>
					<dt>
						&nbsp;
						<input type="hidden" name="redirect" value="' . $session['redirect'] . '" />
						<input type="hidden" name="session_recipe" value="' . $session['recipe_id'] . '" />
						<input type="hidden" name="session_id" value="' . $session['session_id'] . '" />
					</dt>
					<dd>
						<input class="ui-button" type="submit" value="Saugoti" />
						<input class="ui-button" type="button" value="Trinti" onClick="deleteSession(' . $session["session_id"] . ')" />
					</dd>
					<div class="clear"></div>
				</dl>';
			$action = "/brew-session/update";
		}
		return '
			<form method="post" action="' . $action . '" class="brewsession_edit">
				<dl>
					<dt>
						' . $fields['session_name'] . ':
					</dt>
					<dd>
						<input type="text" name="session_name"  style="width:200" value="' . $session["session_name"] . '" />
					</dd>
					<div class="clear"></div>
				</dl>
				<dl>
					<dt>
						' . $fields['user_name'] . ':
					</dt>
					<dd>
						' . $session['user_name'] . '
					</dd>
					<div class="clear"></div>
				</dl>
				<dl>
					<dt>
						' . $fields['recipe_name'] . ':
					</dt>
					<dd>
						<a href="/alus/receptas/' . $session['recipe_id'] . '">
							' . $session["recipe_name"] . '
						</a>
					</dd>
					<div class="clear"></div>
				</dl>
				<dl>
					<dt>
						' . $fields['session_size'] . ':
					</dt>
					<dd>
						<input type="text" name="session_size" style="width:55" value="' . $session["session_size"] . '" />
					</dd>
					<div class="clear"></div>
				</dl>
				<dl>
					<dt>
						' . $fields['session_og'] . ':
					</dt>
					<dd>
						<input type="text" name="session_og" style="width:55" value="' . $session["session_og"] . '" />
					</dd>
					<div class="clear"></div>
				</dl>
				<dl>
					<dt>
						' . $fields['session_fg'] . ':
					</dt>
					<dd>
						<input type="text" name="session_fg" style="width:55" value="' . $session["session_fg"] . '" />
					</dd>
					<div class="clear"></div>
				</dl>
				<dl>
					<dt>
						' . $fields['session_primarydate'] . ':
					</dt>
					<dd>
						<input type="text" name="session_primarydate" style="width:120" id="session_primarydate" value="' . $session["session_primarydate"] . '" />
					</dd>
					<div class="clear"></div>
				</dl>
				<dl>
					<dt>
						' . $fields['session_secondarydate'] . ':
					</dt>
					<dd>
						<input type="text" name="session_secondarydate" style="width:120" id="session_secondarydate" value="' . $session["session_secondarydate"] . '" />
					</dd>
					<div class="clear"></div>
				</dl>
				<dl>
					<dt>
						' . $fields['session_caskingdate'] . ':
					</dt>
					<dd>
						<input type="text" name="session_caskingdate" style="width:120" id="session_caskingdate" value="' . $session["session_caskingdate"] . '" />
					</dd>
					<div class="clear"></div>
				</dl>
				<dl>
					<dt>
						' . $fields['session_comments'] . ':
					</dt>
					<dd>
						<textarea style="width:450;height:250" name="session_comments">' . ($session["session_comments"]) . '</textarea>
					</dd>
					<div class="clear"></div>
				</dl>
				' . $line2 . '
			</form>
			';
	}

	function infoRow($session, $edit = false, $id = 0) {
		$icons = "";
		$icons = '<a href="/brew-session/detail/' . $session['session_id'] . '" alt="Detaliau" title="Detaliau" rel="nofollow" class="list_icon"><span class="ui-icon ui-icon-search"></span></a>';
		if ($edit) {
			$icons .= '<a href="/brew-session/edit/' . $session['session_id'] . '" alt="Redaguoti" title="Redaguoti" rel="nofollow" class="list_icon"><span class="ui-icon ui-icon-pencil"></span></a>';
		}
		if ($session['session_primarydate'] == "0000-00-00") $session['session_primarydate'] = "-";
		if ($session['session_secondarydate'] == "0000-00-00") $session['session_secondarydate'] = "-";
		if ($session['session_caskingdate'] == "0000-00-00") $session['session_caskingdate'] = "-";
		$out = '
			<div class="as-row bs-tr-' . ($id % 2) . '">
				<div class="as-cell">
					' . $session['session_name'] . '
				</div>
				<div class="as-cell">
					<a href="/brewers/' . $session['user_id'] . '">
						' . $session['user_name'] . '
					</a>
				</div>
				<div class="as-cell">
					<a href="/recipes/view/' . $session['recipe_id'] . '">
						' . $session['recipe_name'] . '
					</a>
				</div>
				<div class="as-cell">
					' . $session['session_size'] . '
				</div>
				<div class="as-cell">
					' . $session['session_og'] . '
				</div>
				<div class="as-cell">
					' . $session['session_fg'] . '
				</div>
				<div class="as-cell" style="white-space:nowrap;">
					' . $session['session_primarydate'] . '
				</div>
				<div class="as-cell" style="white-space:nowrap;">
					' . $session['session_secondarydate'] . '
				</div>
				<div class="as-cell" style="white-space:nowrap;">
					' . $session['session_caskingdate'] . '
				</div>
				<div class="as-cell">
					' . $icons . '
				</div>
			</div>
		</form>';
		return $out;
	}

	function infoColumn($session, $owner = false) {
		$fields = array("session_name" => "Virimo pavadinimas", "user_name" => "Aludaris", "recipe_name" => "Receptas", "session_size" => "Alaus kiekis (l.)", "session_og" => "OG", "session_fg" => "FG", "session_primarydate" => "Pirminė fermentacija", "session_secondarydate" => "Antrinė fermentacija", "session_caskingdate" => "Išpilstyta", "session_comments" => "Pastabos");
		$str = "";
		foreach ($fields as $key => $value) {
			if ($key == "user_name") {
				$str.='
					<div class="session_block">
						<div class="session_label">' . $value . ':</div>
						<div class="session_data"><a href="/brewers/' . $session['user_id'] . '">' . $session[$key] . '</a></div>
						<div class="clear"></div>
					</div>';
			} else if ($key == "recipe_name") {
				$str.='
					<div class="session_block">
						<div class="session_label">' . $value . ':</div>
						<div class="session_data"><a href="/alus/receptas/' . $session['recipe_id'] . '">' . $session[$key] . '</a></div>
						<div class="clear"></div>
					</div>';
			} else {
				if ($session[$key] == "0000-00-00") $session[$key] = "-";
				$str.='
					<div class="session_block">
						<div class="session_label">' . $value . ':</div>
						<div class="session_data">' . $session[$key] . '</div>
						<div class="clear"></div>
					</div>';
			}
		}

		return $str;
	}

}

?>
