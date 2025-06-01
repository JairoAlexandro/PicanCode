import React, { useState } from 'react';

export default function PostShow({ data, apiUrl, user }) {
  const [comment, setComment] = useState('');
  const [comments, setComments] = useState(data?.comments || []);
  const [likes, setLikes] = useState(data?.likes || 0);
  const [liked, setLiked] = useState(data?.likedByCurrentUser || false);

  const handleCommentSubmit = async (e) => {
    e.preventDefault();
    if (!comment.trim()) return;

    const res = await fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({ content: comment }),
    });

    if (res.ok) {
      const json = await res.json();
      setComments([...comments, json.comment]);
      setComment('');
    }
  };

  const handleLike = async () => {
    const res = await fetch(apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({}), 
    });

    if (res.ok) {
      const json = await res.json();
      setLiked(json.liked);
      setLikes(prev => prev + (json.liked ? 1 : -1));
    }
  };

  return (
    <div className="max-w-xl mx-auto my-8 bg-[#232323] p-8 rounded-2xl shadow-md">
      <div className="flex flex-col items-center mb-6">
        <div className="text-white text-3xl font-bold text-center mb-2">{data.title}</div>
        <div className="text-gray-400 text-sm text-center">
          Por: <span className="font-bold text-[#e74c3c]">{data.author}</span>
        </div>
        {data.media && (
          <img
            src={`/uploads/posts/${data.media}`}
            alt="Imagen del post"
            className="w-full rounded-2xl mt-4 mb-4 shadow-md"
          />
        )}
      </div>

      <div className="text-[#eeeeee] text-lg text-center mb-6">{data.content}</div>

      <div className="flex items-center justify-center gap-4 mb-6">
        <span className="text-[#e74c3c] font-bold text-xl">
          ♥ {likes} Me gusta
        </span>
        {user && (
          <button
            type="button"
            className={`font-bold text-sm px-4 py-1 rounded-full transition ${
              liked
                ? 'bg-[#e74c3c] text-white'
                : 'text-[#e74c3c] hover:text-white hover:bg-[#e74c3c]'
            }`}
            onClick={handleLike}
          >
            {liked ? 'Quitar Me gusta' : 'Me gusta'}
          </button>
        )}
      </div>

      <hr className="border-gray-600 mb-6" />

      <div className="mt-8">
        <h4 className="text-white text-xl mb-4">Comentarios ({comments.length})</h4>
        {comments.length === 0 ? (
          <p className="text-gray-400">No hay comentarios aún.</p>
        ) : (
          comments.map((c) => (
            <div key={c.id} className="bg-[#292929] p-4 rounded-lg mb-4 shadow-sm">
              <div className="text-sm text-gray-400 mb-1">
                <span className="font-bold text-[#e74c3c]">{c.author}</span>
                <span className="ml-2 text-gray-500 text-xs">{c.createdAt}</span>
              </div>
              <p className="text-[#eee] m-0">{c.content}</p>
            </div>
          ))
        )}

        {user ? (
          <div className="mt-6 bg-[#232323] p-4 rounded-lg shadow-sm">
            <form onSubmit={handleCommentSubmit}>
              <label htmlFor="comment" className="text-white font-bold block mb-2">
                Tu comentario
              </label>
              <textarea
                id="comment"
                value={comment}
                onChange={(e) => setComment(e.target.value)}
                required
                className="w-full min-h-[60px] rounded-md border border-[#3b3b3b] bg-[#1b1b1b] text-white p-3 mb-3 resize-y"
              ></textarea>
              <button
                type="submit"
                className="bg-[#e74c3c] hover:bg-[#c0392b] text-white font-bold py-2 px-4 rounded-md"
              >
                Comentar
              </button>
            </form>
          </div>
        ) : (
          <p className="text-gray-300 mt-4">
            <a href="/login" className="text-[#3498db] hover:text-[#e74c3c] font-bold">
              Inicia sesión
            </a>{' '}
            para comentar.
          </p>
        )}
      </div>
    </div>
  );
}
