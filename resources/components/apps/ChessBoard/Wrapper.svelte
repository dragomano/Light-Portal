<script lang="ts">
  import App from './App.svelte';
  let { props } = $props();
</script>

<App
  id={props.id}
  engineUrl={props.engineUrl}
  assetsUrl={props.assetsUrl}
  boardStyle={props.boardStyle}
  pieceStyle={props.pieceStyle}
  borderType={props.borderType}
  markerType={props.markerType}
  skillLevel={props.skillLevel}
  depth={props.depth}
/>
