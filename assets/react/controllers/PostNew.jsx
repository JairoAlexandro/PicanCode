import React, { useState } from "react";

export default function PostNew({ initialData = {}, apiUrl }) {
  const [snippet, setSnippet] = useState(initialData.content || "");
  const [language, setLanguage] = useState(
    initialData.title || "javascript"
  );
  const [media, setMedia] = useState(null);
  const [errors, setErrors] = useState({ global: [] });

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrors({ global: [] });

    const fd = new FormData();
    fd.append("post[title]", language);
    fd.append("post[content]", snippet);
    if (media) fd.append("post[media]", media);

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
        window.location.href = data.redirectUrl;
      } else {
        setErrors({ global: data.errors || ["Ha ocurrido un error"] });
      }
    } catch (err) {
      setErrors({ global: ["Error de red o respuesta inválida"] });
    }
  };

  return (
    <div className="max-w-xl mx-auto mt-12 bg-[#09090B] p-8 rounded-2xl shadow-xl">
      <h2 className="text-2xl font-bold text-white mb-6 text-center">
        Create Post
      </h2>

      {errors.global.length > 0 && (
        <ul className="mb-4 text-red-400 list-disc list-inside">
          {errors.global.map((err, i) => (
            <li key={i}>{err}</li>
          ))}
        </ul>
      )}

      <form onSubmit={handleSubmit} className="space-y-6">
        <div>
          <label className="block text-gray-300 font-medium mb-2">
            Language
          </label>
          <select
            value={language}
            onChange={(e) => setLanguage(e.target.value)}
            className="w-full bg-gray-800 border border-gray-700 text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600"
          >
            <option value="C#">C#</option>
            <option value="C++">C++</option>
            <option value="CSS">CSS</option>
            <option value="Go">Go</option>
            <option value="HTML">HTML</option>
            <option value="Java">Java</option>
            <option value="JavaScript">JavaScript</option>
            <option value="PHP">PHP</option>
            <option value="Python">Python</option>
            <option value="Ruby">Ruby</option>
            <option value="SQL">SQL</option>
            <option value="Swift">Swift</option>
            <option value="TypeScript">TypeScript</option>
          </select>
        </div>

        <div>
          <label className="block text-gray-300 font-medium mb-2">
            Code Snippet
          </label>
          <textarea
            placeholder="Paste your code here..."
            rows={6}
            className="w-full font-mono bg-gray-800 border border-gray-700 rounded-lg px-4 py-3 text-green-400 placeholder-green-600 focus:outline-none focus:ring-2 focus:ring-red-600"
            value={snippet}
            onChange={(e) => setSnippet(e.target.value)}
          />
        </div>

        <div>
          <label className="block text-gray-300 font-medium mb-2">
            Preview
          </label>
          <pre className="w-full h-48 bg-gray-800 border border-gray-700 rounded-lg p-4 font-mono text-sm text-green-400 overflow-auto whitespace-pre-wrap">
            {snippet.trim() !== ""
              ? snippet
              : "// Preview your code here"}
          </pre>
        </div>

        <div>
          <label className="block text-gray-300 font-medium mb-2">
            Media (Image or Video)
          </label>
          <label
            htmlFor="media-upload"
            className="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-700 rounded-lg cursor-pointer bg-gray-800 hover:border-gray-600 transition"
          >
            <svg
              className="w-8 h-8 text-gray-500 mb-2"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth="2"
                d="M4 7h16M4 12h16M4 17h16"
              />
            </svg>
            <span className="text-gray-500 mb-1">
              Drag &amp; drop or click to browse
            </span>
            <button
              type="button"
              className="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-4 py-1 rounded cursor-pointer"
            >
              Upload Media
            </button>
            <input
              id="media-upload"
              type="file"
              className="hidden"
              onChange={(e) => setMedia(e.target.files[0])}
            />
          </label>
          {media && <p className="mt-2 text-gray-400 text-sm">{media.name}</p>}
        </div>

        <button
          type="submit"
          className="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg transition cursor-pointer"
        >
          Create Post
        </button>
      </form>
    </div>
  );
}
