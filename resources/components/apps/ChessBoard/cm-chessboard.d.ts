declare module 'cm-chessboard/src/Chessboard' {
  export const PIECE: {
    wp: string; wb: string; wn: string; wr: string; wq: string; wk: string;
    bp: string; bb: string; bn: string; br: string; bq: string; bk: string;
  };

  export const PIECE_TYPE: {
    pawn: string; knight: string; bishop: string; rook: string; queen: string; king: string;
  };

  export const PIECES_FILE_TYPE: {
    svgSprite: string;
  };

  export const COLOR: {
    white: string;
    black: string;
  };

  export const INPUT_EVENT_TYPE: {
    moveInputStarted: string;
    validateMoveInput: string;
    moveInputCanceled: string;
    moveInputFinished: string;
  };

  export const POINTER_EVENTS: {
    pointerdown: string;
    pointerup: string;
  };

  export const BORDER_TYPE: {
    none: string;
    thin: string;
    frame: string;
  };

  export const FEN: {
    empty: string;
    start: string;
  };

  export interface ChessboardProps {
    position?: string;
    orientation?: string;
    responsive?: boolean;
    assetsUrl?: string;
    assetsCache?: boolean;
    style?: {
      cssClass?: string;
      showCoordinates?: boolean;
      borderType?: string;
      aspectRatio?: number;
      pieces?: {
        type?: string;
        file?: string;
        tileSize?: number;
      };
      animationDuration?: number;
    };
    extensions?: Array<{ class: any; props?: any }>;
  }

  export class Chessboard {
    constructor(context: HTMLElement, props?: ChessboardProps);

    setPiece(square: string, piece: string, animated?: boolean): Promise<void>;
    movePiece(squareFrom: string, squareTo: string, animated?: boolean): Promise<void>;
    setPosition(fen: string, animated?: boolean): Promise<void>;
    setOrientation(color: string, animated?: boolean): Promise<void>;
    getPiece(square: string): string | null;
    getPosition(): string;
    getOrientation(): string;
    enableMoveInput(eventHandler: (event: any) => boolean, color?: string): void;
    disableMoveInput(): void;
    isMoveInputEnabled(): boolean;
    enableSquareSelect(eventType?: string, eventHandler?: (event: any) => void): void;
    disableSquareSelect(eventType?: string): void;
    isSquareSelectEnabled(): boolean;
    addExtension(extensionClass: any, props?: any): void;
    getExtension(extensionClass: any): any;
    destroy(): void;

    context: HTMLElement;
    id: string;
    extensions: any[];
    props: ChessboardProps;
    state: any;
    view: any;
    positionAnimationsQueue: any;
  }
}

declare module 'cm-chessboard/src/extensions/markers/Markers' {
  export class Markers {
    constructor(chessboard: any, props?: any);
  }

  export const MARKER_TYPE: {
    square: string;
    dot: string;
    circle: string;
  };
}

declare module 'chess.js' {
  export class Chess {
    constructor(fen?: string);
    fen(): string;
    move(move: string | { from: string; to: string; promotion?: string }, options?: { sloppy?: boolean }): any;
    moves(options?: { square?: string; verbose?: boolean }): any[];
    turn(): 'w' | 'b';
    isGameOver(): boolean;
    isCheckmate(): boolean;
    isDraw(): boolean;
    isCheck(): boolean;
    get(square: string): any;
  }
}

interface Window {
  createStockfish: () => Worker;
  Chessboard: typeof import('cm-chessboard/src/Chessboard').Chessboard;
  Chess: typeof import('chess.js').Chess;
  Markers: typeof import('cm-chessboard/src/extensions/markers/Markers').Markers;
  MARKER_TYPE: typeof import('cm-chessboard/src/extensions/markers/Markers').MARKER_TYPE;
}
