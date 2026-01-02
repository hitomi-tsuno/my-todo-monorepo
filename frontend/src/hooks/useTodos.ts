// src/hooks/useTodos.ts

import { useState, useEffect, useRef, useMemo } from "react";
import { Todo } from "../types/todo";
import { Filter } from "../types/filter";

export const useTodos = () => {
  type SortKey = "completed" | "text" | "createdAt"; // ソートキーの型定義 completed: 完了状態、text: 内容、createdAt: 登録日時
  type SortOrder = "asc" | "desc"; // ソート順の型定義 asc: 昇順、desc: 降順

  const [todos, setTodos] = useState<Todo[]>([]);
  const [filter, setFilter] = useState<Filter>("all");
  const isInitialized = useRef(false); // 初期化フラグ
  const [showPopup, setShowPopup] = useState(false); // ポップアップの表示状態
  const checkedCount = useMemo(
    () => todos.filter((t) => t.completed).length,
    [todos]
  ); // チェックされたToDoの数
  const [sortKey, setSortKey] = useState<SortKey>("createdAt");
  const [sortOrder, setSortOrder] = useState<SortOrder>("asc");
  const [searchText, setSearchText] = useState(""); // 検索テキスト

  // 初期読み込み
  useEffect(() => {
    if (isInitialized.current) return;

    const fetchTodos = async () => {
      try {
        const res = await fetch("http://localhost/api/todos.php");
        const data = await res.json();

        // ★ API のデータを React 用に変換する
        const converted = data.map((item: any) => ({
          id: item.id,
          text: item.text,
          completed: item.isdone === 1, // ← isdone を completed に変換
          createdAt: new Date().toISOString(), // DB に無いので仮で作成
          tags: item.tags ?? "",
        }));

        setTodos(converted);
      } catch (error) {
        console.error("API fetch error:", error);
      }
    };
    fetchTodos();
    isInitialized.current = true;
  }, []);

  // 保存処理
  useEffect(() => {
    if (!isInitialized.current) return;
    localStorage.setItem("todos", JSON.stringify(todos));
    console.log("[useEffect保存]todos:", todos);
  }, [todos]);

  // 追加
  const addTodo = async (text: string) => {
    if (!text.trim()) return;

    try {
      const res = await fetch("http://localhost/api/todos.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          text,
          tags: "", // 必要なら後で対応
        }),
      });

      const result = await res.json();

      if (result.success) {
        // API が返した ID を使ってフロント側にも反映
        const newTodo: Todo = {
          id: result.id,
          text,
          completed: false,
          createdAt: new Date().toISOString(), // DB に createdAt がないのでフロント側で作成
        };

        setTodos((prev) => [...prev, newTodo]);
      }
    } catch (error) {
      console.error("addTodo API error:", error);
    }
  };
  // 削除
  const deleteTodo = async (id: number) => {
    try {
      const target = todos.find((todo) => todo.id === id);
      if (!target) return; // 念のため安全に

      const url = new URL("http://localhost/api/todos.php");
      url.searchParams.append("id", id.toString());

      const res = await fetch(url.toString(), {
        method: "DELETE",
        headers: { "Content-Type": "application/json" },
      });

      const result = await res.json();

      if (result.success) {
        setTodos((prev) => prev.filter((todo) => todo.id !== id));
      }
    } catch (error) {
      console.error("deleteTodo API error:", error);
    }
  };

  // 完了切り替え
  const toggleTodo = async (id: number) => {
    try {
      const target = todos.find((todo) => todo.id === id);
      if (!target) return; // 念のため安全に

      const newIsDone = target.completed ? 0 : 1;

      const url = new URL("http://localhost/api/todos.php");
      url.searchParams.append("id", id.toString());

      const res = await fetch(url.toString(), {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          isdone: newIsDone,
        }),
      });

      const result = await res.json();

      if (result.success) {
        setTodos((prev) =>
          prev.map((todo) =>
            todo.id === id ? { ...todo, completed: !todo.completed } : todo
          )
        );
      }
    } catch (error) {
      console.error("toggleTodo API error:", error);
    }
  };

  // 編集
  const updateTodo = async (id: number, newText: string) => {
    try {
      const target = todos.find((todo) => todo.id === id);
      if (!target) return; // 念のため安全に

      const url = new URL("http://localhost/api/todos.php");
      url.searchParams.append("id", id.toString());

      const res = await fetch(url.toString(), {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          text: newText,
        }),
      });

      const result = await res.json();

      if (result.success) {
        setTodos((prev) =>
          prev.map((todo) =>
            todo.id === id ? { ...todo, text: newText } : todo
          )
        );
      }
    } catch (error) {
      console.error("updateTodo API error:", error);
    }
  };

  // 一括削除
  const deleteChecked = async () => {
    try {
      const res = await fetch("http://localhost/api/todos.php", {
        method: "DELETE",
      });

      const result = await res.json();

      if (result.success) {
        setTodos((prev) => prev.filter((todo) => !todo.completed));
      }
    } catch (error) {
      console.error("deleteCompleted API error:", error);
    }
  };

  // 一括完了・未完了
  const checkAll = () => {
    setTodos((prev) => prev.map((todo) => ({ ...todo, completed: true })));
  };
  const uncheckAll = () => {
    setTodos((prev) => prev.map((todo) => ({ ...todo, completed: false })));
  };

  // フィルター処理
  const applyFilter = (todos: Todo[], filter: Filter, searchText: string) => {
    let result: Todo[];

    switch (filter) {
      case "completed":
        result = todos.filter((t) => t.completed);
        break;
      case "incomplete":
        result = todos.filter((t) => !t.completed);
        break;
      default:
        result = todos;
    }
    if (searchText.trim() !== "") {
      result = result.filter((t) => t.text.includes(searchText.trim()));
    }

    console.log(
      `[applyFilter] filter: ${filter}, result: ${result},  searchText: ${searchText}`
    );
    return result;
  };

  const filteredTodos = useMemo(() => {
    const result = applyFilter(todos, filter, searchText);
    return result;
  }, [todos, filter, searchText]);

  // ソート処理
  const sortedTodos = useMemo(() => {
    const result = [...filteredTodos].sort((a, b) => {
      let compare = 0;
      switch (sortKey) {
        case "completed":
          compare = Number(a.completed) - Number(b.completed);
          break;
        case "text":
          compare = a.text.localeCompare(b.text);
          break;
        case "createdAt":
          compare = a.createdAt.localeCompare(b.createdAt);
          break;
      }
      return sortOrder === "asc" ? compare : -compare;
    });

    console.log(
      `[sortedTodos] sortKey: ${sortKey}, sortOrder: ${sortOrder}, result:`,
      result
    );
    return result;
  }, [filteredTodos, sortKey, sortOrder]);

  // ソートキー・順序の設定
  const handleSort = (key: SortKey) => {
    if (sortKey === key) {
      // 同じキーなら昇順⇔降順を切り替え
      setSortOrder(sortOrder === "asc" ? "desc" : "asc");
    } else {
      // 新しいキーなら昇順で開始
      setSortKey(key);
      setSortOrder("asc");
    }
  };

  return {
    todos,
    filteredTodos,
    sortedTodos,
    filter,
    setFilter,
    addTodo,
    deleteTodo,
    toggleTodo,
    updateTodo,
    deleteChecked,
    checkAll,
    uncheckAll,
    checkedCount,
    showPopup,
    setShowPopup,
    handleSort,
    sortKey,
    sortOrder,
    searchText,
    setSearchText,
  };
};
