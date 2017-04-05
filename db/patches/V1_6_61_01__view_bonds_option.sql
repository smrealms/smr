-- Set permission to view bonds on the Planet List in alliance roles
ALTER TABLE `alliance_has_roles` ADD `view_bonds` enum('TRUE','FALSE')
  NOT NULL DEFAULT 'FALSE';
