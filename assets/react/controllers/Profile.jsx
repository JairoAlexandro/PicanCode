import { data } from "autoprefixer";
import React, { useState } from "react";

export default function Profile({ initialData, apiUrl }) {
  const [user, setUser] = useState(initialData.user);
  const [posts, setPosts] = useState(initialData.posts);
  const [isFollowing, setIsFollowing] = useState(initialData.isFollowing);
  const [csrfToken, setCsrfToken] = useState(initialData.csrfToken);
  const [canFollow] = useState(initialData.canFollow);
  const [errors, setErrors] = useState([]);

  const formatDate = (iso) => new Date(iso).toLocaleDateString("es-ES");

  const toggleFollow = async () => {
    setErrors([]);
    const fd = new FormData();
    fd.append("_token", csrfToken);
    fd.append("follow", isFollowing ? "0" : "1");

    try {
      const res = await fetch(apiUrl, {
        method: "POST",
        body: fd,
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          Accept: "application/json",
        },
        credentials: "same-origin",
      });
      const data = await res.json();

      if (data.success) {
        setIsFollowing(data.isFollowing);
        setCsrfToken(data.csrfToken);
      } else {
        setErrors(data.errors || ["Ha ocurrido un error"]);
      }
    } catch (err) {
      setErrors(["Error de red o respuesta inválida"]);
    }
  };

  return (
    <div className="max-w-5xl mx-auto bg-gray-900 rounded-lg shadow-lg overflow-hidden">
      <div className="bg-[#0E0E0E] px-6 py-8 flex flex-col lg:flex-row items-center lg:items-end justify-between">
        <div className="flex items-center space-x-6">
          {user.avatar ? (
            <img
              src={`/uploads/avatars/${user.avatar}`}
              alt={user.username}
              className="w-36 h-36 rounded-full object-cover border-4 border-gray-700 shadow-md"
            />
          ) : (
            <div className="w-36 h-36 rounded-full bg-gray-700 flex items-center justify-center text-gray-400 text-5xl border-4 border-gray-700 shadow-md">
              {user.username.charAt(0).toUpperCase()}
            </div>
          )}
          <div>
            <h1 className="text-4xl font-bold text-white">{user.username}</h1>
          </div>
        </div>

        <div className="mt-6 lg:mt-0">
          {canFollow ? (
            <button
              onClick={toggleFollow}
              className={`px-6 py-2 font-medium rounded-full shadow transition-colors ${
                isFollowing
                  ? "bg-red-500 text-white hover:bg-red-600"
                  : "bg-blue-500 text-white hover:bg-blue-600"
              }`}
            >
              {isFollowing ? "Dejar de seguir" : "Seguir"}
            </button>
          ) : (
            <a
              href={`/user/${user.id}/edit`}
              className="inline-flex px-6 py-2 font-medium rounded-full shadow bg-green-600 hover:bg-green-700 text-white transition"
            >
              🖉 Editar perfil
            </a>
          )}
        </div>
      </div>

      <div className="px-6 py-4 bg-black text-center lg:text-left">
        <p className="text-gray-300 text-base max-w-2xl mx-auto lg:mx-0">
          {user.bio || "Este usuario no ha puesto biografía."}
        </p>
        <p className="text-gray-500 text-sm mt-2 flex items-center justify-center lg:justify-start space-x-2">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="16"
            height="16"
            fill="currentColor"
            className="bi bi-calendar3 flex-shrink-0"
            viewBox="0 0 16 16"
          >
            <path d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857z" />
            <path d="M6.5 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2" />
          </svg>
          <span>Miembro desde {formatDate(user.createdAt)}</span>
        </p>
      </div>

      <div className="px-6 py-4 bg-black flex justify-around text-center">
        <div>
          <span className="block text-white font-bold text-lg">
            {posts.length}
          </span>
          <span className="text-gray-400 text-sm">Posts</span>
        </div>
        <div>
          <span className="block text-white font-bold text-lg">
            {user.followers}
          </span>
          <span className="text-gray-400 text-sm">Seguidores</span>
        </div>
        <div>
          <span className="block text-white font-bold text-lg">
            {user.following}
          </span>
          <span className="text-gray-400 text-sm">Siguiendo</span>
        </div>
      </div>

      <hr className="border-black" />

      <div className="px-6 py-8 bg-black">
        <h2 className="text-2xl font-semibold text-white mb-6 text-center">
          Mis publicaciones
        </h2>
        {posts.length > 0 ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {posts.map((p) => (
              <div
                key={p.id}
                className="bg-[#09090B] rounded-lg shadow-md overflow-hidden flex flex-col"
              >
                {p.media && (
                  <div className="mb-4 p-2 h-48 w-full overflow-hidden rounded">
                  <img
                    src={`/uploads/posts/${p.media}`}
                    alt={p.title}
                    className="w-full h-full object-contain"
                  />
                </div>
                )}
                <div className="p-4 flex-1 flex flex-col">
                  <h3 className="text-white font-semibold mb-2 text-center">
                    {p.title}
                  </h3>
                  <p className="text-gray-300 text-sm mb-4 flex-1 text-center">
                    {p.snippet}
                  </p>
                  <div className="flex items-center justify-between">
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
                    <div>{p.likes}</div>
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
                    <div>{p.comments}</div>
                  </div>
                </div>
                    <a
                      href={`/posts/${p.id}`}
                      className="text-blue-400 hover:underline text-sm"
                    >
                      Ver
                    </a>
                  </div>
                </div>
              </div>
            ))}
          </div>
        ) : (
          <p className="text-gray-500 text-center">No ha publicado nada aún.</p>
        )}
      </div>
    </div>
  );
}
