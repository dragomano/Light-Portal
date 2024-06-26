export default {
  title: {
    title: 'Enhanced Readability',
    titleAriaLabel: 'Enhanced Readability',
  },
  layoutSwitch: {
    title: 'Layout Switch',
    titleHelpMessage:
      'Adjust the layout style of the site to adapt to different reading needs and screens.',
    titleAriaLabel: 'Layout Switch',
    titleScreenNavWarningMessage: 'No available layout can be switched in mobile screen.',
    optionFullWidth: 'Expand all',
    optionFullWidthAriaLabel: 'Expand all',
    optionFullWidthHelpMessage:
      'The sidebar and content area occupy the entire width of the screen.',
    optionSidebarWidthAdjustableOnly: 'Expand sidebar with adjustable values',
    optionSidebarWidthAdjustableOnlyAriaLabel: 'Expand sidebar with adjustable values',
    optionSidebarWidthAdjustableOnlyHelpMessage:
      'Expand sidebar width and add a new slider for user to choose and customize their desired width of the maximum width of sidebar can go, but the content area width will remain the same.',
    optionBothWidthAdjustable: 'Expand all with adjustable values',
    optionBothWidthAdjustableAriaLabel: 'Expand all with adjustable values',
    optionBothWidthAdjustableHelpMessage:
      'Expand both sidebar and document content and add two new slider for user to choose and customize their desired width of the maximum width of either sidebar or document content can go.',
    optionOriginalWidth: 'Original width',
    optionOriginalWidthAriaLabel: 'Original width',
    optionOriginalWidthHelpMessage: 'The original layout width of the site',
    contentLayoutMaxWidth: {
      title: 'Content Layout Max Width',
      titleAriaLabel: 'Content Layout Max Width',
      titleHelpMessage:
        'Adjust the exact value of the document content width of the site layout to adapt to different reading needs and screens.',
      titleScreenNavWarningMessage:
        'Content Layout Max Width is not available in mobile screen temporarily.',
      slider: 'Adjust the maximum width of the content layout',
      sliderAriaLabel: 'Adjust the maximum width of the content layout',
      sliderHelpMessage:
        'A ranged slider for user to choose and customize their desired width of the maximum width of the content layout can go.',
    },
    pageLayoutMaxWidth: {
      title: 'Page Layout Max Width',
      titleAriaLabel: 'Page Layout Max Width',
      titleHelpMessage:
        'Adjust the exact value of the page width of the site layout to adapt to different reading needs and screens.',
      titleScreenNavWarningMessage:
        'Page Layout Max Width is not available in mobile screen temporarily.',
      slider: 'Adjust the maximum width of the page layout',
      sliderAriaLabel: 'Adjust the maximum width of the page layout',
      sliderHelpMessage:
        'A ranged slider for user to choose and customize their desired width of the maximum width of the page layout can go.',
    },
  },
  spotlight: {
    title: 'Spotlight',
    titleAriaLabel: 'Spotlight',
    titleHelpMessage:
      'Highlight the line where the mouse is currently hovering in the content to optimize for users who may have reading and focusing difficulties.',
    titleScreenNavWarningMessage: 'Spotlight is not available in mobile screen temporarily.',
    optionOn: 'On',
    optionOnAriaLabel: 'On',
    optionOnHelpMessage: 'Turn on Spotlight.',
    optionOff: 'Off',
    optionOffAriaLabel: 'Off',
    optionOffHelpMessage: 'Turn off Spotlight.',
    styles: {
      title: 'Spotlight Styles',
      titleAriaLabel: 'Spotlight Styles',
      titleHelpMessage: 'Adjust the styles of Spotlight.',
      titleScreenNavWarningMessage:
        'Spotlight Styles is not available in mobile screen temporarily.',
      optionUnder: 'Under',
      optionUnderAriaLabel: 'Under',
      optionUnderHelpMessage:
        'Add a solid background color underneath the hovering element to highlight where the cursor is currently hovering.',
      optionAside: 'Aside',
      optionAsideAriaLabel: 'Aside',
      optionAsideHelpMessage:
        'Add a fixed line with solid color aside the hovering element to highlight where the cursor is currently hovering.',
    },
  },
};
