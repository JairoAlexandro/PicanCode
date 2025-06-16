import React, { useState, useEffect } from "react";

export default function ProfileEdit({ initialData = {}, apiUrl }) {
  const [bio, setBio] = useState(initialData.bio || "");
  const [avatarFile, setAvatarFile] = useState(null);
  const [avatarPreview, setAvatarPreview] = useState(initialData.avatar || null);
  const [errors, setErrors] = useState([]);

  useEffect(() => {
    if (!avatarFile) {
      setAvatarPreview(initialData.avatar || null);
      return;
    }
    const reader = new FileReader();
    reader.onload = () => setAvatarPreview(reader.result);
    reader.readAsDataURL(avatarFile);
  }, [avatarFile, initialData.avatar]);

  const handleAvatarChange = (e) => {
    const file = e.target.files[0];
    setAvatarFile(file);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrors([]);

    const formData = new FormData();
    formData.append("profile_edit[bio]", bio);
    if (avatarFile) {
      formData.append("profile_edit[avatar]", avatarFile);
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
        setErrors(data.errors || ["Ha ocurrido un error al guardar."]);
      }
    } catch {
      setErrors(["Error de red o respuesta inválida."]);
    }
  };

  return (
    <div className="max-w-xl mx-auto mt-12 bg-[#09090B] p-8 rounded-2xl shadow-xl">
      <h2 className="text-2xl font-bold text-white mb-6 text-center">
        Editar perfil
      </h2>

      {errors.length > 0 && (
        <ul className="mb-4 text-red-400 list-disc list-inside">
          {errors.map((err, i) => (
            <li key={i}>{err}</li>
          ))}
        </ul>
      )}

      <form onSubmit={handleSubmit} className="space-y-6">
        <div className="text-center">
          {avatarPreview ? (
            <img
              src={avatarPreview}
              alt="Avatar Preview"
              className="w-24 h-24 rounded-full object-cover mx-auto shadow-md"
              loading="lazy"
            />
          ) : (
            <div className="w-24 h-24 rounded-full bg-gray-700 text-gray-400 flex items-center justify-center mx-auto shadow-md text-3xl">
              <i className="fas fa-user" />
            </div>
          )}
        </div>

        <div>
          <label className="block text-gray-300 font-medium mb-2">
            Biografía
          </label>
          <textarea
            rows={4}
            value={bio}
            onChange={(e) => setBio(e.target.value)}
            className="w-full bg-gray-800 border border-gray-700 text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600"
            placeholder="Escribe tu biografía..."
          />
        </div>

        <div>
          <label className="block text-gray-300 font-medium mb-2">
            Avatar (opcional)
          </label>
          <label
            htmlFor="avatar-upload"
            className="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-700 rounded-lg cursor-pointer bg-gray-800 hover:border-gray-600 transition"
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
            <span className="text-gray-500">Arrastra o haz clic para seleccionar</span>
            <input
              id="avatar-upload"
              type="file"
              accept="image/*"
              className="hidden"
              onChange={handleAvatarChange}
            />
          </label>
        </div>

        <button
          type="submit"
          className="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition cursor-pointer"
        >
          Guardar cambios
        </button>
      </form>
    </div>
  );
}
