import { createGlobalStyle } from 'styled-components';

export const GlobalStyle = createGlobalStyle`
  /* CSSリセットやその他のグローバルスタイルをここに記述 */
  /* 例: */
  body {
    background-color: white; /* 全体の背景色を設定 */
    margin: 0;
    padding: 0;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen',
      'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue',
      sans-serif;
  }

  a {
    color: inherit;
    text-decoration: none;
  }
`;
export default GlobalStyle;