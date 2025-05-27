import React, { useState } from 'react';

export default function Profile({ initialData, apiUrl }) {
  const [user, setUser] = useState(initialData.user);
  const [posts, setPosts] = useState(initialData.posts);
  const [isFollowing, setIsFollowing] = useState(initialData.isFollowing);
  const [csrfToken, setCsrfToken] = useState(initialData.csrfToken);
  const [canFollow] = useState(initialData.canFollow);
  const [errors, setErrors] = useState([]);

  const formatDate = iso =>
    new Date(iso).toLocaleDateString('es-ES');

  const toggleFollow = async () => {
    setErrors([]);
    const fd = new FormData();
    fd.append('_token', csrfToken);
    fd.append('follow', isFollowing ? '0' : '1');

    try {
      const res = await fetch(apiUrl, {
        method: "POST",
        body:   fd,
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          "Accept":           "application/json",
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
    <div className="max-w-4xl mx-auto p-6 bg-gray-800 rounded-xl shadow-lg">
      <div className="flex flex-col items-center mb-4">
        {user.avatar ? (
          <img
            src={`/uploads/avatars/${user.avatar}`}
            alt={user.username}
            className="w-32 h-32 rounded-full object-cover shadow-md mb-4"
          />
        ) : (
          <div className="w-32 h-32 rounded-full bg-gray-700 flex items-center justify-center text-gray-400 text-4xl mb-4">
            {user.username.charAt(0).toUpperCase()}
          </div>
        )}
        <h1 className="text-3xl font-bold text-white mb-1">
          {user.username}
        </h1>
        <p className="text-gray-400 text-sm">
          Miembro desde {formatDate(user.createdAt)}
        </p>
        {user.bio && (
          <p className="text-gray-300 mt-2 text-center max-w-md">
            {user.bio}
          </p>
        )}

        {errors.length > 0 && (
          <div className="mt-2 text-red-400 text-sm">
            {errors.map((e,i) => <p key={i}>• {e}</p>)}
          </div>
        )}

        {canFollow ? (
          <button
            onClick={toggleFollow}
            className={`mt-4 px-6 py-2 text-sm font-medium rounded-full shadow transition-colors ${
              isFollowing
                ? 'bg-red-500 text-white hover:bg-red-600'
                : 'bg-blue-500 text-white hover:bg-blue-600'
            }`}
          >
            {isFollowing ? 'Dejar de seguir' : 'Seguir'}
          </button>
        ) : (
          <a
            href={`/user/${user.id}/editar`}
            className="mt-4 inline-flex px-6 py-2 text-sm font-medium rounded-full shadow bg-green-600 hover:bg-green-700 text-white transition"
          >
            🖉 Editar perfil
          </a>
        )}
      </div>

      <hr className="border-gray-700 mb-6" />

      <h2 className="text-xl font-semibold text-white mb-4 text-center">
        Mis publicaciones
      </h2>
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        {posts.length > 0 ? (
          posts.map(p => (
            <div
              key={p.id}
              className="bg-gray-700 p-4 rounded-lg shadow-md flex flex-col"
            >
              {p.media && (
                <img
                  src={`/uploads/posts/${p.media}`}
                  alt={p.title}
                  className="w-full h-40 object-cover rounded mb-3"
                />
              )}
              <h3 className="text-white font-semibold mb-2 text-center">
                {p.title}
              </h3>
              <p className="text-gray-300 text-sm mb-4 flex-1 text-center">
                {p.snippet}
              </p>
              <div className="flex items-center justify-between">
                <span className="text-red-400 font-bold">
                  ♥ {p.likes}
                </span>
                <a
                  href={`/posts/${p.id}`}
                  className="text-blue-400 hover:underline text-sm"
                >
                  Ver
                </a>
              </div>
            </div>
          ))
        ) : (
          <p className="text-gray-500 text-center col-span-full">
            No ha publicado nada aún.
          </p>
        )}
      </div>
    </div>
  );
}
