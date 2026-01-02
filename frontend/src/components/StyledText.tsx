import styled from "styled-components";

const StyledText = styled.span<{ checked: boolean }>`
  text-decoration: ${({ checked }) => (checked ? "line-through" : "none")};
  cursor: pointer;
  position: relative;

  &:hover::after {
    content: "✏️";
    position: absolute;
    right: -20px;
    font-size: 0.8rem;
  }
`;

export default StyledText;
