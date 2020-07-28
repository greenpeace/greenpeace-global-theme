export const addSubAndSuperscript = function (wp){
	const el = wp.element.createElement

	/* Superscript icon */
	const superscriptIcon = wp.element.createElement( 'svg',
	  {
		  width: 20,
		  height: 20,
	  },
	  wp.element.createElement( 'path',
		{
			d: 'm 11.451024,15.991879 v 1.900559 H 8.6286372 L 6.8191261,15.024528 6.5459915,14.546546 Q 6.4549468,14.444115 6.4208029,14.307552 h -0.03414 l -0.1024233,0.238994 q -0.113806,0.227613 -0.284517,0.500744 L 4.2357343,17.892438 H 1.2995409 V 15.991879 H 2.7562552 L 4.9982324,12.68013 2.8928217,9.5846081 H 1.3336801 V 7.6726705 h 3.1410444 l 1.5819027,2.5947735 q 0.022772,0.04552 0.2617521,0.477987 0.091045,0.102421 0.1251849,0.23899 h 0.03414 q 0.034139,-0.102432 0.1251845,-0.23899 L 6.8874061,10.267444 8.4806875,7.6726705 H 11.405498 V 9.5846081 H 9.982924 l -2.0940281,3.0386179 2.3216401,3.368653 z m 7.249435,-7.7274175 v 2.3444015 h -5.849622 l -0.03415,-0.307279 q -0.04552,-0.318656 -0.04552,-0.5235031 0,-0.7283595 0.295896,-1.3315288 0.295896,-0.6031748 0.739738,-0.9844236 0.443844,-0.3812499 0.95597,-0.7397383 0.512126,-0.3584877 0.955968,-0.6202403 0.443845,-0.2617558 0.739742,-0.6145521 0.295891,-0.3527973 0.295891,-0.7283591 0,-0.4324593 -0.335727,-0.711285 -0.335727,-0.2788267 -0.80233,-0.2788267 -0.580409,0 -1.103918,0.4438464 Q 14.35307,4.3381578 14.102696,4.645436 L 12.907742,3.5984202 q 0.295895,-0.4210809 0.716976,-0.7511204 0.944588,-0.7397375 2.139551,-0.7397375 1.251867,0 2.025745,0.6771454 0.773881,0.6771454 0.773881,1.8038232 0,0.6373144 -0.278825,1.1722012 -0.278822,0.5348868 -0.705595,0.8706133 -0.426771,0.3357272 -0.927519,0.6657667 -0.500747,0.3300361 -0.933209,0.5747178 -0.43246,0.2446817 -0.74543,0.5861006 -0.312966,0.3414178 -0.347105,0.7169762 H 17.26651 V 8.2644615 Z',
		},
	  ),
	)

	/* Subscript icon */
	const subscriptIcon = wp.element.createElement( 'svg',
	  {
		  width: 20,
		  height: 20,
	  },
	  wp.element.createElement( 'path',
		{
			d: 'M 11.415572,11.723303 V 13.59208 H 8.6403806 L 6.8611266,10.772129 6.5925593,10.302137 Q 6.5030369,10.201425 6.4694659,10.067141 H 6.4358949 L 6.3351822,10.302137 Q 6.2232813,10.525943 6.0554268,10.79451 L 4.3209317,13.59208 H 1.4338398 V 11.723303 H 2.8661965 L 5.0706822,8.4669311 3.0004802,5.4231745 H 1.4674107 V 3.5432079 h 3.0885176 l 1.5554501,2.5513836 q 0.022381,0.044757 0.2573749,0.4699921 0.089522,0.1007124 0.1230932,0.2349956 h 0.033571 Q 6.5589882,6.6988668 6.6485107,6.5645836 L 6.9282683,6.0945915 8.4949066,3.5432079 H 11.370811 V 5.4231745 H 9.9720247 L 7.9130129,8.4109791 10.195831,11.723303 Z m 7.150588,2.428291 v 2.305198 h -5.751805 l -0.04476,-0.302137 q -0.03357,-0.503563 -0.03357,-0.514753 0,-0.716178 0.290947,-1.309263 0.290948,-0.593086 0.727368,-0.96796 0.436422,-0.374874 0.939986,-0.727368 0.503561,-0.352494 0.939983,-0.60987 0.43642,-0.257377 0.727368,-0.604275 0.290947,-0.3469 0.290947,-0.716178 0,-0.425232 -0.330112,-0.699394 -0.330115,-0.2741619 -0.788916,-0.2741619 -0.570706,0 -1.085457,0.4364219 -0.156665,0.123093 -0.402851,0.425231 L 12.870301,9.5635781 q 0.290948,-0.414039 0.70499,-0.738558 0.895222,-0.727368 2.103773,-0.727368 1.230931,0 1.991869,0.665822 0.760939,0.665821 0.760939,1.7736599 0,0.738558 -0.386064,1.326048 -0.386065,0.587489 -0.939984,0.962363 -0.553918,0.374875 -1.113434,0.699393 -0.559513,0.324518 -0.973554,0.704988 -0.41404,0.380469 -0.458801,0.816891 h 2.596145 v -0.895223 z',
		},
	  ),
	)

	/**
	 * Add a button for subscript (<sub>) in Gutenberg rich text
	 */
	var SubscriptButton = function( props ) {
		return wp.element.createElement(
		  wp.blockEditor.RichTextToolbarButton, {
			  icon: subscriptIcon,
			  title: 'Subscript',
			  isActive: props.isActive,
			  onClick: function() {
				  props.onChange( wp.richText.toggleFormat(
					props.value,
					{ type: 'planet4-blocks/subscript' },
				  ) )
			  },
			  className: 'toolbar-button-planet4-subscript',
		  },
		)
	}

	wp.richText.registerFormatType(
	  'planet4-blocks/subscript', {
		  title: 'Subscript',
		  tagName: 'sub',
		  className: null,
		  edit: SubscriptButton,
	  },
	)

	/**
	 * Add a button for superscript (<sup>) in Gutenberg rich text
	 */
	var SuperscriptButton = function( props ) {
		return wp.element.createElement(
		  wp.blockEditor.RichTextToolbarButton, {
			  icon: superscriptIcon,
			  title: 'Superscript',
			  isActive: props.isActive,
			  onClick: function() {
				  props.onChange( wp.richText.toggleFormat(
					props.value,
					{ type: 'planet4-blocks/superscript' },
				  ) )
			  },
		  },
		)
	}

	wp.richText.registerFormatType(
	  'planet4-blocks/superscript', {
		  title: 'Superscript',
		  tagName: 'sup',
		  className: null,
		  edit: SuperscriptButton,
	  },
	)
};
