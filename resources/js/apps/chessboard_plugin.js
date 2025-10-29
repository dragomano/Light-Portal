import { mount } from 'svelte'
import './../i18n.js'
import ChessGame from '../../components/apps/ChessBoard/Wrapper.svelte'

/** @type {NodeListOf<HTMLElement>} */
const chessBlocks = document.querySelectorAll('.chess_board');

chessBlocks.forEach((element) => {
  if (!element.dataset.mounted) {
    let componentData = window.portalJson;

    if (element.dataset.apiData) {
      const apiData = JSON.parse(element.dataset.apiData);
      componentData = { ...apiData.context, txt: apiData.txt };
    }

    mount(ChessGame, {
      target: element,
      props: { props: componentData }
    });

    element.dataset.mounted = 'true';
  }
});
