import React, { useState, useRef, useEffect } from "react";

export default function PostShow({ data, apiUrl, user }) {
  const [comment, setComment] = useState("");
  const [comments, setComments] = useState(data?.comments || []);
  const [likes, setLikes] = useState(data?.likes || 0);
  const [liked, setLiked] = useState(data?.likedByCurrentUser || false);
  const [copied, setCopied] = useState(false);
  const [isImageOpen, setIsImageOpen] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);

  const menuRef = useRef(null);
  const isAuthor = user && user.id === data.authorId;

  useEffect(() => {
    const handleClickOutside = (e) => {
      if (menuRef.current && !menuRef.current.contains(e.target)) {
        setMenuOpen(false);
      }
    };
    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  const handleCopyText = () => {
    const textToCopy = data.content;
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard
        .writeText(textToCopy)
        .then(() => {
          setCopied(true);
          setTimeout(() => setCopied(false), 2000);
        })
        .catch((err) => console.error("Error Clipboard API:", err));
    } else {
      const textarea = document.createElement("textarea");
      textarea.value = textToCopy;
      textarea.style.position = "fixed";
      textarea.style.top = "-9999px";
      document.body.appendChild(textarea);
      textarea.focus();
      textarea.select();
      try {
        document.execCommand("copy");
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
      } catch (err) {
        console.error("Fallback copy:", err);
      }
      document.body.removeChild(textarea);
    }
  };

  const handleCommentSubmit = async (e) => {
    e.preventDefault();
    if (!comment.trim()) return;

    const res = await fetch(apiUrl, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify({ content: comment }),
    });

    if (res.ok) {
      const json = await res.json();
      setComments([...comments, json.comment]);
      setComment("");
    }
  };

  const handleLike = async () => {
    const res = await fetch(apiUrl, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify({}),
    });

    if (res.ok) {
      const json = await res.json();
      setLiked(json.liked);
      setLikes((prev) => prev + (json.liked ? 1 : -1));
    }
  };

  const handleDelete = async () => {
    if (!confirm("¿Eliminar publicación?")) return;

    const res = await fetch(`/posts/${data.id}`, {
      method: "DELETE",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    });

    if (res.ok) {
      window.location.href = "/";
    } else {
      alert("Error al eliminar la publicación");
    }
  };

  const formatDate = (iso) => new Date(iso).toLocaleDateString("es-ES");

  return (
    <>
      {isImageOpen && (
        <div
          className="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50"
          onClick={() => setIsImageOpen(false)}
        >
          <img
            src={`/uploads/posts/${data.media}`}
            alt="Imagen ampliada"
            className="max-w-full max-h-full rounded-lg shadow-lg"
            onClick={(e) => e.stopPropagation()}
          />
        </div>
      )}

      <div className="bg-[#09090B] rounded-lg shadow-md max-w-2xl mx-auto my-10 p-6 flex flex-col relative">
        {isAuthor && (
          <div className="absolute top-4 right-4" ref={menuRef}>
            <button
              onClick={() => setMenuOpen(!menuOpen)}
              className="text-gray-500 hover:text-gray-300 focus:outline-none cursor-pointer"
            >
              <span className="text-2xl leading-none">⋮</span>
            </button>
            {menuOpen && (
              <div className="absolute right-0 mt-2 w-32 bg-[#1e1e1e] border border-[#3b3b3b] rounded shadow-lg z-10">
                <a
                  href={`/posts/${data.id}/edit`}
                  className="block px-4 py-2 text-sm text-[#3498db] hover:bg-[#2a2a2a]"
                >
                  Editar
                </a>
                <button
                  onClick={() => {
                    setMenuOpen(false);
                    handleDelete();
                  }}
                  className="w-full text-left px-4 py-2 text-sm text-[#e74c3c] hover:bg-[#2a2a2a] cursor-pointer"
                >
                  Eliminar
                </button>
              </div>
            )}
          </div>
        )}

        <header className="flex items-center space-x-3 mb-4">
          <a href={`/user/${data.authorId}`}>
            <img
              src={
                data.avatar
                  ? `/uploads/avatars/${data.avatar}`
                  : "/images/profile.png"
              }
              alt={`${data.author} avatar`}
              className="w-10 h-10 rounded-full object-cover"
            />
          </a>
          <a
            href={`/user/${data.authorId}`}
            className="text-gray-300 font-medium hover:underline"
          >
            {data.author}
          </a>
          <span className="text-gray-500 text-sm ml-2">
            {formatDate(data.createdAt)}
          </span>
        </header>

        {data.media && (
          <div className="mb-4 cursor-pointer">
            <img
              src={`/uploads/posts/${data.media}`}
              alt={data.title}
              className="w-full h-60 object-contain rounded-lg shadow-md hover:opacity-90 transition-opacity duration-200"
              onClick={() => setIsImageOpen(true)}
            />
          </div>
        )}

        <h2 className="text-white text-2xl font-semibold mb-3">{data.title}</h2>

        <div className="flex justify-end mb-2">
          <button
            onClick={handleCopyText}
            className="text-gray-400 hover:text-gray-200 focus:outline-none"
            aria-label="Copiar contenido completo"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              fill="currentColor"
              className="bi bi-copy"
              viewBox="0 0 16 16"
            >
              <path
                fillRule="evenodd"
                d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z"
              />
            </svg>
          </button>
          {copied && (
            <span className="ml-2 text-green-400 font-medium">
              Texto copiado
            </span>
          )}
        </div>

        <pre className="text-sm font-mono text-green-400 bg-[#1e1e1e] border border-[#3b3b3b] rounded-lg p-4 mb-6 max-h-48 overflow-auto whitespace-pre-wrap">
          {data.content.trim() !== ""
            ? data.content
            : "// Aquí se mostrará el contenido del post"}
        </pre>

        <div className="flex items-center justify-start mb-4 px-2 gap-6">
          <div
            className="flex items-center font-bold gap-1 cursor-pointer"
            onClick={handleLike}
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="18"
              height="18"
              viewBox="0 0 16 16"
              className="bi bi-heart"
              fill={liked ? "#e74c3c" : "#FFFFFF"}
              stroke={liked ? "#e74c3c" : "#FFFFFF"}
            >
              <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15" />
            </svg>
            <span className={liked ? "text-[#e74c3c]" : "text-white"}>
              {likes}
            </span>
          </div>

          <div className="flex items-center text-gray-400 font-bold gap-1">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="18"
              height="18"
              fill="currentColor"
              className="bi bi-chat"
              viewBox="0 0 16 16"
            >
              <path d="M2.678 11.894a1 1 0 0 1 .287.801 11 11 0 0 1-.398 2c1.395-.323 2.247-.697 2.634-.893a1 1 0 0 1 .71-.074A8 8 0 0 0 8 14c3.996 0 7-2.807 7-6s-3.004-6-7-6-7 2.808-7 6c0 1.468.617 2.83 1.678 3.894m-.493 3.905a22 22 0 0 1-.713.129c-.2.032-.352-.176-.273-.362a10 10 0 0 0 .244-.637l.003-.01c.248-.72.45-1.548.524-2.319C.743 11.37 0 9.76 0 8c0-3.866 3.582-7 8-7s8 3.134 8 7-3.582 7-8 7a9 9 0 0 1-2.347-.306c-.52.263-1.639.742-3.468 1.105" />
            </svg>
            <span>{comments.length}</span>
          </div>
        </div>
      </div>

      <div className="bg-[#09090B] rounded-lg shadow-md max-w-2xl mx-auto mb-10 p-6 flex flex-col">
        <h4 className="text-white text-xl font-semibold mb-4">Comentarios</h4>

        {comments.length === 0 ? (
          <p className="text-gray-400 mb-4">No hay comentarios aún.</p>
        ) : (
          <div className="space-y-4 mb-6 max-h-60 overflow-y-auto">
            {comments.map((c) => (
              <div
                key={c.id}
                className="bg-[#292929] p-4 rounded-lg shadow-sm hover:bg-[#333333] transition"
              >
                <div className="flex justify-between mb-1">
                  <span className="font-bold text-[#e74c3c]">{c.author}</span>
                  <span className="text-gray-500 text-xs">{c.createdAt}</span>
                </div>
                <p className="text-[#eee]">{c.content}</p>
              </div>
            ))}
          </div>
        )}

        {user ? (
          <div className="bg-[#0f0f0f] p-4 rounded-lg shadow-sm">
            <form onSubmit={handleCommentSubmit}>
              <label
                htmlFor="comment"
                className="text-white font-semibold block mb-2"
              >
                Tu comentario
              </label>
              <textarea
                id="comment"
                value={comment}
                onChange={(e) => setComment(e.target.value)}
                required
                className="w-full min-h-[80px] rounded-md border border-[#3b3b3b] bg-[#1b1b1b] text-white p-3 mb-3 resize-y focus:outline-none focus:ring-2 focus:ring-[#e74c3c]"
              ></textarea>
              <button
                type="submit"
                className="bg-red-600 hover:bg-red-700 text-white font-bold py-3 w-full rounded-md transition cursor-pointer"
              >
                Comentar
              </button>
            </form>
          </div>
        ) : (
          <p className="text-gray-300 mt-4 text-center">
            <a
              href="/login"
              className="text-[#3498db] hover:text-[#e74c3c] font-bold"
            >
              Inicia sesión
            </a>{" "}
            para comentar.
          </p>
        )}
      </div>
    </>
  );
}
