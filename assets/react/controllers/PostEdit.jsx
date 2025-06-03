import React, { useState, useEffect } from "react";

export default function PostEdit({ initialData, apiUrl }) {
  const [language, setLanguage] = useState(initialData.title || "javascript");
  const [content, setContent] = useState(initialData.content || "");
  const [mediaFile, setMediaFile] = useState(null);
  const [mediaPreview, setMediaPreview] = useState(initialData.media || null);
  const [errors, setErrors] = useState([]);

  // Cargar URL de media actual vía AJAX si no viene en initialData
  useEffect(() => {
    if (!initialData.media) {
      fetch(apiUrl, {
        headers: { "X-Requested-With": "XMLHttpRequest" },
      })
        .then((res) => res.json())
        .then((json) => {
          if (json.data && json.data.media) {
            setMediaPreview(json.data.media);
          }
        })
        .catch(() => {
          // Ignorar fallos de preview
        });
    }
  }, [apiUrl, initialData.media]);

  const handleMediaChange = (e) => {
    const file = e.target.files[0];
    setMediaFile(file);
    if (file) {
      const reader = new FileReader();
      reader.onload = () => setMediaPreview(reader.result);
      reader.readAsDataURL(file);
    } else {
      setMediaPreview(initialData.media || null);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrors([]);

    const formData = new FormData();
    formData.append("post[title]", language);
    formData.append("post[content]", content);
    if (mediaFile) {
      formData.append("post[media]", mediaFile);
    }

    try {
      const res = await fetch(apiUrl, {
        method: "POST",
        body: formData,
        headers: { "X-Requested-With": "XMLHttpRequest" },
        credentials: "same-origin",
      });
      const data = await res.json();
      if (data.success) {
        window.location.href = data.redirectUrl;
      } else {
        setErrors(data.errors || ["Ha ocurrido un error al guardar el post."]);
      }
    } catch {
      setErrors(["Error de red o respuesta inválida."]);
    }
  };

  return (
    <div className="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-xl mx-auto bg-[#09090B] p-8 rounded-2xl shadow-xl">
        <h2 className="text-2xl font-bold text-white mb-6 text-center">
          Editar Post
        </h2>

        {errors.length > 0 && (
          <ul className="mb-4 text-red-400 list-disc list-inside">
            {errors.map((err, i) => (
              <li key={i}>{err}</li>
            ))}
          </ul>
        )}

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* LENGUAJE */}
          <div>
            <label className="block text-gray-300 font-medium mb-2">
              Lenguaje
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

          {/* CONTENIDO */}
          <div>
            <label className="block text-gray-300 font-medium mb-2">
              Código
            </label>
            <textarea
              rows={6}
              value={content}
              onChange={(e) => setContent(e.target.value)}
              className="w-full font-mono bg-gray-800 border border-gray-700 text-green-400 placeholder-green-600 px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600"
              placeholder="// Escribe o pega tu código aquí..."
            />
          </div>

          {/* PREVIEW DE CÓDIGO */}
          <div>
            <label className="block text-gray-300 font-medium mb-2">
              Vista previa
            </label>
            <pre className="w-full h-48 bg-gray-800 border border-gray-700 rounded-lg p-4 font-mono text-sm text-green-400 overflow-auto whitespace-pre-wrap">
              {content.trim() !== "" ? content : "// Verás tu código aquí..."}
            </pre>
          </div>

          {/* MEDIA */}
          <div>
            <label className="block text-gray-300 font-medium mb-2">
              Media (Imagen o Video)
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
                Arrastra o haz clic para seleccionar
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
                accept="image/*,video/*"
                className="hidden"
                onChange={handleMediaChange}
              />
            </label>
            {mediaPreview && (
              <div className="mt-2">
                {mediaPreview.startsWith("data:") ? (
                  mediaFile && mediaFile.type.startsWith("image/") ? (
                    <img
                      src={mediaPreview}
                      alt="Preview"
                      className="w-full h-auto rounded-lg border border-gray-700 shadow-sm"
                    />
                  ) : mediaFile && mediaFile.type.startsWith("video/") ? (
                    <video
                      src={mediaPreview}
                      controls
                      className="w-full h-auto rounded-lg border border-gray-700 shadow-sm"
                    />
                  ) : initialData.media ? (
                    <img
                      src={initialData.media}
                      alt="Preview existente"
                      className="w-full h-auto rounded-lg border border-gray-700 shadow-sm"
                    />
                  ) : null
                ) : (
                  <img
                    src={mediaPreview}
                    alt="Preview existente"
                    className="w-full h-auto rounded-lg border border-gray-700 shadow-sm"
                  />
                )}
              </div>
            )}
          </div>

          {/* BOTÓN GUARDAR */}
          <div>
            <button
              type="submit"
              className="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg transition cursor-pointer"
            >
              Guardar Cambios
            </button>
          </div>
        </form>

        {/* Enlace para volver al post */}
        <div className="mt-6 text-center">
          <a
            href={`/posts/${initialData.id}`}
            className="text-red-600 hover:text-red-500 font-bold transition"
          >
            « Volver
          </a>
        </div>
      </div>
    </div>
  );
}
