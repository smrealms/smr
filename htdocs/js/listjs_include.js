/** global: List */

var listjs = (function() {

	function defaultList(id, names) {
		var list = new List(id, {
			valueNames: names,
			sortFunction: function(a, b, options) {
				// strip HTML tags and commas
				return list.utils.naturalSort(a.values()[options.valueName].replace(/<.*?>|,/g, ''), b.values()[options.valueName].replace(/<.*?>|,/g, ''), options);
			}
		});
	}

	var listjs = {};

	listjs.PlanetList = function() {
		defaultList('planet-list', ['sort_name', 'sort_lvl', 'sort_owner', 'sort_sector', 'sort_build']);
	};

	listjs.PlanetListFinancial = function() {
		defaultList('planet-list', ['sort_name', 'sort_lvl', 'sort_owner', 'sort_sector', 'sort_credits', 'sort_bonds', 'sort_interest', 'sort_mature']);
	};

	listjs.alliance_forces = function() {
		defaultList('forces-list', ['sort_name', 'sort_sector', 'sort_cds', 'sort_sds', 'sort_mines', {name: 'sort_expire', attr: 'data-expire'}]);
	};

	listjs.alliance_list = function() {
		defaultList('alliance-list', ['sort_name', 'sort_totExp', 'sort_avgExp', 'sort_members']);
	};

	listjs.alliance_message = function() {
		defaultList('topic-list', ['sort_topic', 'sort_author', 'sort_replies', {name: 'sort_lastReply', attr: 'data-lastReply'}]);
	};

	listjs.alliance_roster = function() {
		defaultList('alliance-roster', [{name: 'sort_name', attr: 'data-name'}, 'sort_race', 'sort_experience', 'sort_role', 'sort_status']);
	};

	listjs.combat_log_list = function() {
		defaultList('logs-list', ['sort_date', 'sort_sectorid', 'sort_attacker', 'sort_defender']);
	};

	listjs.council_list = function() {
		defaultList('council-members', [{name: 'sort_name', attr: 'data-name'}, 'sort_alliance', 'sort_experience']);
	};

	listjs.current_players = function() {
		defaultList('cpl', [{name: 'sort_name', attr: 'data-name'}, 'sort_race', 'sort_alliance', 'sort_exp']);
	};

	listjs.forces_list = function() {
		defaultList('forces-list', ['sort_sector', 'sort_cds', 'sort_sds', 'sort_mines', {name: 'sort_expire', attr: 'data-expire'}]);
	};

	listjs.message_view = function() {
		defaultList('folders', ['sort_name', 'sort_messages']);
	};

	listjs.shop_weapon = function() {
		defaultList('weapon-list', ['sort_name', 'sort_shield', 'sort_armor', 'sort_acc', 'sort_race', 'sort_power', 'sort_cost']);
	};

	listjs.alliance_pick = function() {
		defaultList('draft-pick', ['sort_name', 'sort_race', 'sort_hof', 'sort_score']);
	};

	return listjs;

})();
