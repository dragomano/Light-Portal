const oPageCommentsToggle = new smc_Toggle({
	bToggleEnabled: true,
	bCurrentlyCollapsed: isCurrentlyCollapsed,
	aSwappableContainers: [
		"page_comments"
	],
	aSwapImages: [
		{
			sId: "page_comments_toggle",
			altExpanded: toggleAltExpandedTitle,
			altCollapsed: toggleAltCollapsedTitle
		}
	],
	aSwapLinks: [
		{
			sId: "page_comments_link",
			msgExpanded: toggleMsgBlockTitle,
			msgCollapsed: toggleMsgBlockTitle
		}
	],
	oThemeOptions: {
		bUseThemeSettings: useThemeSettings,
		sOptionName: "collapse_header_page_comments",
		sSessionId: smf_session_id,
		sSessionVar: smf_session_var
	},
	oCookieOptions: {
		bUseCookie: useCookie,
		sCookieName: "upshrinkPC"
	}
});