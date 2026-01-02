// src/components/StyledInput.tsx
import styled from "styled-components";

const StyledInput = styled.input`
//   font-size: 16px;
  padding: 6px 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
//   width: 100%;
  box-sizing: border-box;

  &:focus {
    outline: none;
    border-color: #888;
  }
`;

export default StyledInput;
