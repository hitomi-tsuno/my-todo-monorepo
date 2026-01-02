// src/components/TodoList.tsx
import React from "react";
import { Todo } from "../types/todo"; // TODOの型定義
import TodoItem from "./TodoItem"; // TodoItemコンポーネント
import StyledTableHeader from "./StyledTableHeader"; // スタイリングされたテーブルヘッダーコンポーネント

interface Props {
  filteredTodos: Todo[];
  sortedTodos: Todo[];
  deleteTodo: (id: number) => void; // propsの型定義
  toggleTodo: (id: number) => void; // propsの型定義
  updateTodo: (id: number, newText: string) => void; // propsの型定義
  checkedCount: number;
  checkAll: () => void;
  uncheckAll: () => void;
  handleSort: (key: "completed" | "text" | "createdAt") => void;
  sortKey: string;
  sortOrder: "asc" | "desc";
}

const TodoList: React.FC<Props> = ({
  filteredTodos,
  sortedTodos,
  deleteTodo,
  toggleTodo,
  updateTodo,
  checkedCount,
  checkAll,
  uncheckAll,
  handleSort,
  sortKey,
  sortOrder,
}) => {
  return (
    <table>
      {/* ヘッダ部 */}
      <thead>
        <tr>
          {/* ＜完了＞ */}
          <StyledTableHeader onClick={() => handleSort("completed")}>
            <input
              type="checkbox"
              checked={checkedCount === filteredTodos.length}
              onChange={(e) => (e.target.checked ? checkAll() : uncheckAll())}
            />
            完了
            {sortKey === "completed" ? (sortOrder === "asc" ? "▲" : "▼") : "⇅"}{" "}
          </StyledTableHeader>

          {/* ＜内容＞ */}
          <StyledTableHeader onClick={() => handleSort("text")}>
            内容 {sortKey === "text" ? (sortOrder === "asc" ? "▲" : "▼") : "⇅"}
          </StyledTableHeader>

          {/* ＜登録日時＞ */}
          <StyledTableHeader onClick={() => handleSort("createdAt")}>
            登録日時
            {sortKey === "createdAt" ? (sortOrder === "asc" ? "▲" : "▼"): "⇅"}
          </StyledTableHeader>

          {/* ＜操作＞ */}
          <StyledTableHeader>操作</StyledTableHeader>
        </tr>
      </thead>

      {/* ボディ部 */}
      <tbody>
        {filteredTodos.length === 0 ? (
          <tr>
            <td colSpan={3}>表示するTODOがありません</td>
          </tr>
        ) : (
          sortedTodos.map((todo) => (
            <TodoItem
              key={todo.id}
              todo={todo}
              deleteTodo={deleteTodo}
              toggleTodo={toggleTodo}
              updateTodo={updateTodo}
            />
          ))
        )}
      </tbody>
    </table>
  );
};

export default TodoList;
