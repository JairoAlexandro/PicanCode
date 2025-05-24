import React from 'react';

export default function PostIndex({ apiUrl, initialData }) {
  const { view, posts } = initialData;

  const formatDate = iso => new Date(iso).toLocaleDateString('es-ES');

  return (
    <section className="py-8">
      <div className="text-center mb-8">
        {['recent', 'following'].map(v => (
          <a
            key={v}
            href={`${apiUrl}?view=${v}`}
            className={`inline-block m-2 px-6 py-3 rounded font-bold transition-colors ${
              view === v
                ? 'bg-blue-500 text-white'
                : 'bg-gray-700 text-white hover:bg-blue-600'
            }`}
          >
            {v === 'recent' ? 'Recientes' : 'Siguiendo'}
          </a>
        ))}
      </div>

      <h2 className="text-white text-2xl text-center mb-6">
        {view === 'following'
          ? 'Publicaciones de a quienes sigues'
          : 'Publicaciones recientes'}
      </h2>

      <div className="flex flex-wrap gap-8 justify-center">
        {posts && posts.length > 0 ? (
          posts.map(post => (
            <article
              key={post.id}
              className="bg-gray-900 p-6 rounded-lg max-w-sm flex flex-col shadow-md"
            >
              {post.media && (
                <div className="mb-4">
                  <img
                    src={`/uploads/posts/${post.media}`}
                    alt={post.title}
                    className="w-full rounded mb-2"
                  />
                </div>
              )}

              <div className="flex-1">
                <h3 className="text-white text-xl font-semibold mb-2">
                  <a href={`/posts/${post.id}`} className="hover:underline">
                    {post.title}
                  </a>
                </h3>
                <p className="text-gray-500 text-sm mb-2">
                  Por{' '}
                  <a
                    href={`/user/${post.authorId}`}
                    className="text-gray-300 hover:underline"
                  >
                    {post.author}
                  </a>{' '}
                  · {formatDate(post.createdAt)}
                </p>
                <p className="text-gray-400 mb-4">
                  {post.snippet}
                </p>
              </div>

              <footer className="flex justify-between items-center mt-4">
                <span className="text-red-500 font-bold">
                  ♥ {post.likes}
                </span>
                <a
                  href={`/posts/${post.id}`}
                  className="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
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
