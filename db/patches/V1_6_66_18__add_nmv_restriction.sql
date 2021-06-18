-- Give NMV the "Newbie" buyer restriction
UPDATE ship_type SET buyer_restriction = 3 WHERE ship_name = 'Newbie Merchant Vessel';
