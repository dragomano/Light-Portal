let oPageCommentsToggle = new smc_Toggle({
	bToggleEnabled: true,
	bCurrentlyCollapsed: is_currently_collapsed,
	aSwappableContainers: [
		"page_comments"
	],
	aSwapImages: [
		{
			sId: "page_comments_toggle",
			altExpanded: toggle_alt_expanded_title,
			altCollapsed: toggle_alt_collapsed_title
		}
	],
	aSwapLinks: [
		{
			sId: "page_comments_link",
			msgExpanded: toggle_msg_block_title,
			msgCollapsed: toggle_msg_block_title
		}
	],
	oThemeOptions: {
		bUseThemeSettings: use_theme_settings,
		sOptionName: "collapse_header_page_comments",
		sSessionId: smf_session_id,
		sSessionVar: smf_session_var
	},
	oCookieOptions: {
		bUseCookie: use_cookie,
		sCookieName: "upshrinkPC"
	}
});