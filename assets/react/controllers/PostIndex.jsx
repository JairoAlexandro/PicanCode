import React, { useState } from "react";

export default function PostIndex({ apiUrl, initialData }) {
  const { view, posts } = initialData;
  const [copiedId, setCopiedId] = useState(null);

  const formatDate = (iso) => new Date(iso).toLocaleDateString("es-ES");

  const handleCopy = (text, id) => {
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard
        .writeText(text)
        .then(() => {
          setCopiedId(id);
          setTimeout(() => setCopiedId(null), 2000);
        })
        .catch(() => fallbackCopy(text, id));
    } else {
      fallbackCopy(text, id);
    }
  };

  const fallbackCopy = (text, id) => {
    const textarea = document.createElement("textarea");
    textarea.value = text;
    textarea.style.position = "fixed";
    textarea.style.top = "-9999px";
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();
    try {
      document.execCommand("copy");
      setCopiedId(id);
      setTimeout(() => setCopiedId(null), 2000);
    } catch (err) {
      console.error("Fallback: Oops, no se pudo copiar", err);
    }
    document.body.removeChild(textarea);
  };

  return (
    <section className="py-8">
      <div className="text-center mb-8">
        {["recent", "following"].map((v) => (
          <a
            key={v}
            href={`${apiUrl}?view=${v}`}
            className={`inline-block m-2 px-6 py-3 rounded font-bold transition-colors ${
              view === v
                ? "bg-blue-500 text-white"
                : "bg-gray-700 text-white hover:bg-blue-600"
            }`}
          >
            {v === "recent" ? "Recientes" : "Siguiendo"}
          </a>
        ))}
      </div>

      <h2 className="text-white text-2xl text-center mb-6">
        {view === "following"
          ? "Publicaciones de a quienes sigues"
          : "Publicaciones recientes"}
      </h2>

      <div className="flex flex-wrap gap-8 justify-center">
        {posts && posts.length > 0 ? (
          posts.map((post) => (
            <article
              key={post.id}
              className="bg-[#09090B] p-6 rounded-lg max-w-sm flex flex-col shadow-md"
            >
              <header className="mb-4">
                <div className="flex items-center justify-between mb-2">
                  <div className="flex items-center space-x-3">
                    <a href={`/user/${post.authorId}`}>
                      <img
                        src={
                          post.avatar
                            ? `/uploads/avatars/${post.avatar}`
                            : "/images/profile.png"
                        }
                        alt={`${post.author} avatar`}
                        className="w-8 h-8 rounded-full"
                        loading="lazy"
                      />
                    </a>
                    <a
                      href={`/user/${post.authorId}`}
                      className="text-gray-300 font-medium"
                    >
                      {post.author}
                    </a>
                  </div>
                  <p className="text-gray-500 text-sm">
                    {formatDate(post.createdAt)}
                  </p>
                </div>
              </header>

              {post.media && (
                <div className="mb-4 h-68 w-84 overflow-hidden rounded">
                  <img
                    src={`/uploads/posts/${post.media}`}
                    alt={post.title}
                    className="w-full h-full object-contain rounded-lg"
                    loading="lazy"
                  />
                </div>
              )}

              <div className="flex-1">
                <div className="flex items-center justify-between mb-1">
                  <h3 className="text-white text-xl font-semibold">
                    <a href={`/posts/${post.id}`} className="hover:underline">
                      {post.title}
                    </a>
                  </h3>
                  <div className="flex items-center">
                    <button
                      onClick={() => handleCopy(post.snippet, post.id)}
                      className="text-gray-400 hover:text-gray-200 focus:outline-none cursor-pointer"
                      aria-label="Copiar snippet"
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
                    {copiedId === post.id && (
                      <span className="ml-2 text-green-400 font-medium">
                        Texto copiado
                      </span>
                    )}
                  </div>
                </div>
                <p className="text-gray-400 text-sm">
                  {post.snippet.length > 150
                    ? `${post.snippet.slice(0, 150)}…`
                    : post.snippet}
                </p>
              </div>

              <footer className="flex justify-between items-center mt-4 w-full">
                <div className="flex items-center space-x-4">
                  <div className="flex items-center text-red-500 font-bold gap-1">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      fill="currentColor"
                      className="bi bi-heart"
                      viewBox="0 0 16 16"
                    >
                      <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15" />
                    </svg>
                    <div>{post.likes}</div>
                  </div>

                  <div className="flex items-center text-gray-400 font-bold gap-1">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      fill="currentColor"
                      className="bi bi-chat"
                      viewBox="0 0 16 16"
                    >
                      <path d="M2.678 11.894a1 1 0 0 1 .287.801 11 11 0 0 1-.398 2c1.395-.323 2.247-.697 2.634-.893a1 1 0 0 1 .71-.074A8 8 0 0 0 8 14c3.996 0 7-2.807 7-6s-3.004-6-7-6-7 2.808-7 6c0 1.468.617 2.83 1.678 3.894m-.493 3.905a22 22 0 0 1-.713.129c-.2.032-.352-.176-.273-.362a10 10 0 0 0 .244-.637l.003-.01c.248-.72.45-1.548.524-2.319C.743 11.37 0 9.76 0 8c0-3.866 3.582-7 8-7s8 3.134 8 7-3.582 7-8 7a9 9 0 0 1-2.347-.306c-.52.263-1.639.742-3.468 1.105" />
                    </svg>
                    <div>{post.commentsCount}</div>
                  </div>
                </div>

                <a
                  href={`/posts/${post.id}`}
                  className="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600"
                >
                  Ver
                </a>
              </footer>
            </article>
          ))
        ) : (
          <p className="text-gray-500 text-center w-full">
            No hay publicaciones para mostrar.
          </p>
        )}
      </div>
    </section>
  );
}
