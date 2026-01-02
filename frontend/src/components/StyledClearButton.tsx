// src/components/StyledClearButton.tsx
// "×"クリアボタンのスタイリングコンポーネント
import styled from "styled-components";

const StyledClearButton = styled.button`
  position: absolute;
  right: 4px;
  top: 50%;
  transform: translateY(-50%);
  border: none;
  background: transparent;
  cursor: pointer;
  font-size: 16px;
  color: #888;

  &:hover {
    color: #333;
  }
`;

export default StyledClearButton;
