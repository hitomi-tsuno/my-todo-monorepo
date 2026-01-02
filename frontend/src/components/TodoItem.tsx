// src/components/TodoItem.tsx
import React, { useState, useRef } from "react";
import { Todo } from "../types/todo"; // TODOの型定義
import StyledInput from "../components/StyledInput"; // スタイリングされた入力コンポーネント
import StyledButton from "../components/StyledButton"; // スタイリングされたボタンコンポーネント
import StyledDate from "../components/StyledDate"; // スタイリングされた日付コンポーネント
import StyledText from "../components/StyledText"; // スタイリングされたテキストコンポーネント
import StyledClearButton from "../components/StyledClearButton"; // "×"スタイリングされたクリアボタンコンポーネント

interface Props {
  todo: Todo;
  deleteTodo: (id: number) => void; // propsの型定義
  toggleTodo: (id: number) => void; // propsの型定義
  updateTodo: (id: number, newText: string) => void; // propsの型定義
}

const TodoItem: React.FC<Props> = ({
  todo,
  deleteTodo,
  toggleTodo,
  updateTodo,
}) => {
  const [isEditing, setIsEditing] = useState(false); // 編集モードの状態管理
  const [editText, setEditText] = useState(todo.text); // 編集中のテキスト管理
  const [prevEditText, setPrevEditText] = useState(""); // 前回の検索テキスト管理
  const inputRef = useRef<HTMLInputElement>(null); // テキスト入力のref

  // 編集確定処理
  const handleUpdate = () => {
    console.log("[handleUpdate:内容編集確定] editText:", editText);
    const trimmed = editText.trim();
    if (trimmed === "") return;
    updateTodo(todo.id, trimmed);
    setIsEditing(false);
  };

  return (
    <tr>
      <td>
        {/* ＜完了＞チェックボックス */}
        <input
          type="checkbox"
          checked={todo.completed}
          onChange={(e) => toggleTodo(todo.id)}
        />
      </td>

      <td>
        {/* ＜TODOの内容＞テキストボックス　または　text */}
        {isEditing ? (
          <div style={{ position: "relative", display: "inline-block" }}>
            <StyledInput
              ref={inputRef}
              type="text"
              value={editText}
              onChange={(e) => setEditText(e.target.value)}
              onBlur={handleUpdate}
              onKeyDown={(e) => {
                if (e.key === "Enter") handleUpdate();
              }}
              autoFocus
            />
            {/* ×ボタン 編集前に戻す*/}
            <StyledClearButton
              // onClickだとテキストボックスのonBlurが優先されるため、onMouseDownで対応
              onMouseDown={(e) => {
                e.preventDefault(); // フォーカスが外れるのを防ぐ
                setEditText(prevEditText); // 編集前に戻す
                inputRef.current?.focus(); // テキストボックスにフォーカス移動
              }}
            >
              ×
            </StyledClearButton>{" "}
          </div>
        ) : (
          <StyledText
            checked={todo.completed}
            onClick={
              () => {
                setIsEditing(true);
                setPrevEditText(todo.text);
              } // 編集前のテキストを保存
            }
            title="クリックして編集"
          >
            {todo.text}
          </StyledText>
        )}
      </td>

      <td>
        {/* ＜登録日時＞ */}
        <StyledDate>登録日時: {todo.createdAt}</StyledDate>
      </td>

      <td>
        {/* ＜削除＞ボタン */}
        <StyledButton
          // onClickだとフォーカスが外れてしまうため、onMouseDownで対応
          onMouseDown={(e) => {
            e.preventDefault(); // フォーカスを維持
            deleteTodo(todo.id);
          }}
        >
          削除
        </StyledButton>
      </td>
    </tr>
  );
};

export default TodoItem;
