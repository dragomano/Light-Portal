<script lang="ts">
  interface Props {
    image: string;
    status: "closed" | "opened" | "matched";
    disabled?: boolean;
    onclick?: () => void;
  }

  let { image, status, disabled, ...rest }: Props = $props();
</script>

<div class={`card ${status} ${disabled ? "disabled" : ""}`} {...rest}>
  <div class="card-inner">
    <div class="card-face card-back"></div>
    <div class="card-face card-front">
      <img src={image} alt="" />
    </div>
  </div>
</div>

<style lang="scss" scoped>
  .card {
    height: 120px;
    perspective: 1000px;
    cursor: pointer;
    transition: transform 0.3s ease-in-out;
  
    &.opened .card-inner,
    &.matched .card-inner {
      transform: rotateY(180deg);
    }

    &.disabled {
      pointer-events: none;
    }
  }

  .card-inner {
    position: relative;
    width: 100%;
    height: 100%;
    transform-style: preserve-3d;
    transition: transform 0.6s ease-in-out;
  }

  .card-face {
    position: absolute;
    width: 100%;
    height: 100%;
    backface-visibility: hidden;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }

  .card-back {
    background-color: #3498db;
    border: 2px solid #2980b9;
  }

  .card-front {
    background-color: #fff;
    border: 2px solid #e0e0e0;
    transform: rotateY(180deg);
  }

  .card-front img {
    width: 60%;
    height: 60%;
    object-fit: contain;
  }

  .card.matched .card-front {
    background-color: #e6ffe6;
    border-color: #2ecc71;
  }
</style>
