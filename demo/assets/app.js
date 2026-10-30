/* Gestor de contactos — demo estática
 * Sin backend. Los datos viven en localStorage y todo el CSV se procesa en el navegador.
 */
(() => {
    'use strict';

    const STATE_KEY = 'gestor-contactos-demo-state-v1';
    const SESSION_KEY = 'gestor-contactos-demo-session-v1';
    const THEME_KEY = 'gestor-contactos-demo-theme-v1';

    const ROLES = {
        admin: { label: 'Administración', short: 'Admin', userId: 'u1' },
        editor: { label: 'Edición', short: 'Editor', userId: 'u2' },
        viewer: { label: 'Lectura', short: 'Lector', userId: 'u3' },
    };

    const ICONS = {
        created: 'plus', updated: 'edit', deleted: 'trash', imported: 'upload',
        note_added: 'sparkle', login: 'shield',
    };

    const FIELDS = [
        { key: 'name', label: 'Nombre', required: true, aliases: ['nombre', 'nombre y apellido', 'nombre completo', 'contacto', 'name', 'full name', 'nombre del contacto'] },
        { key: 'email', label: 'Correo', aliases: ['correo', 'correo electronico', 'email', 'e mail', 'mail'] },
        { key: 'phone', label: 'Teléfono', aliases: ['telefono', 'telefono celular whatsapp', 'telefono celular', 'celular', 'whatsapp', 'movil', 'phone', 'mobile'] },
        { key: 'company', label: 'Empresa', aliases: ['empresa', 'compania', 'company', 'organizacion'] },
        { key: 'job_title', label: 'Cargo', aliases: ['cargo', 'puesto', 'posicion', 'job title', 'position'] },
        { key: 'contact_type', label: 'Tipo B2B/B2C', aliases: ['tipo de cliente', 'tipo cliente', 'b2b b2c', 'b2b', 'b2c', 'contact type'] },
        { key: 'business_type', label: 'Tipo de negocio', aliases: ['tipo de negocio', 'tipo negocio', 'sector', 'industria', 'business type'] },
        { key: 'gender', label: 'Género', aliases: ['genero', 'sexo', 'gender'] },
        { key: 'city', label: 'Ciudad', aliases: ['ciudad', 'city'] },
        { key: 'province', label: 'Provincia', aliases: ['provincia', 'estado', 'region', 'state'] },
        { key: 'country', label: 'País', aliases: ['pais', 'country'] },
        { key: 'birthdate', label: 'Cumpleaños', aliases: ['cumpleanos', 'fecha de nacimiento', 'nacimiento', 'birthdate', 'birthday', 'date of birth'] },
        { key: 'source', label: 'Origen', aliases: ['origen', 'origen del contacto', 'fuente', 'source'] },
        { key: 'associated_brand', label: 'Marca asociada', aliases: ['marca asociada', 'marca', 'brand'] },
        { key: 'associated_company', label: 'Empresa asociada', aliases: ['empresa asociada', 'associated company'] },
    ];

    const SEGMENT_COLORS = ['#C65D3B', '#7A8B6F', '#D9A441', '#8C6A9E', '#4F7C8A', '#B56576', '#5C7A5C', '#A9784E'];

    /* ---------- Utilidades ---------- */

    const $ = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

    const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (ch) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]));

    const icon = (name, cls = '') => `<svg class="${cls}" aria-hidden="true"><use href="#i-${name}"/></svg>`;

    const uid = (prefix = 'x') => `${prefix}${Date.now().toString(36)}${Math.random().toString(36).slice(2, 6)}`;

    const normalize = (text) => String(text ?? '')
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();

    const digits = (value) => String(value ?? '').replace(/[^\d]/g, '');

    const slug = (text) => normalize(text).replace(/\s+/g, '.');

    const todayISO = () => {
        const d = new Date();
        d.setHours(0, 0, 0, 0);
        return d.toISOString().slice(0, 10);
    };

    const addDays = (date, days) => {
        const copy = new Date(date.getTime());
        copy.setDate(copy.getDate() + days);
        return copy;
    };

    const formatDate = (iso, options = { day: 'numeric', month: 'short', year: 'numeric' }) => {
        if (!iso) return '—';
        const date = new Date(`${iso}T12:00:00`);
        if (Number.isNaN(date.getTime())) return '—';
        return date.toLocaleDateString('es-PA', options);
    };

    const relativeTime = (iso) => {
        const date = new Date(iso);
        const diff = Date.now() - date.getTime();
        const minutes = Math.floor(diff / 60000);
        if (minutes < 1) return 'ahora';
        if (minutes < 60) return `hace ${minutes} min`;
        const hours = Math.floor(minutes / 60);
        if (hours < 24) return `hace ${hours} h`;
        const days = Math.floor(hours / 24);
        if (days < 7) return `hace ${days} d`;
        return formatDate(iso.slice(0, 10));
    };

    const initials = (name) => String(name || '?').trim().split(/\s+/).slice(0, 2).map((part) => part[0]?.toUpperCase() || '').join('');

    const colorFor = (text) => SEGMENT_COLORS[Math.abs([...String(text)].reduce((acc, ch) => acc + ch.charCodeAt(0), 0)) % SEGMENT_COLORS.length];

    const avatar = (name, extra = '') => `<span class="avatar ${extra}" style="--tone:${colorFor(name)}">${esc(initials(name))}</span>`;

    const parseDateInput = (value) => {
        const raw = String(value ?? '').trim();
        if (!raw) return null;
        let year; let month; let day;
        let match = raw.match(/^(\d{4})-(\d{1,2})-(\d{1,2})/);
        if (match) [, year, month, day] = match;
        if (!match && (match = raw.match(/^(\d{1,2})[/-](\d{1,2})[/-](\d{4})$/))) {
            [, day, month, year] = match;
        }
        if (!year) return null;
        const date = new Date(Number(year), Number(month) - 1, Number(day));
        if (date.getFullYear() !== Number(year) || date.getMonth() !== Number(month) - 1 || date.getDate() !== Number(day)) return null;
        if (date > new Date() || date.getFullYear() < 1900) return null;
        return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    };

    const daysUntilBirthday = (birthdate) => {
        if (!birthdate) return null;
        const today = new Date(); today.setHours(0, 0, 0, 0);
        const born = new Date(`${birthdate}T12:00:00`);
        let next = new Date(today.getFullYear(), born.getMonth(), born.getDate());
        if (next < today) next = new Date(today.getFullYear() + 1, born.getMonth(), born.getDate());
        return Math.round((next - today) / 86400000);
    };

    const ageOf = (birthdate) => {
        if (!birthdate) return null;
        const born = new Date(`${birthdate}T12:00:00`);
        const today = new Date();
        let age = today.getFullYear() - born.getFullYear();
        const before = today.getMonth() < born.getMonth() || (today.getMonth() === born.getMonth() && today.getDate() < born.getDate());
        if (before) age--;
        return age;
    };

    const csvEscapeCell = (value) => {
        let text = String(value ?? '');
        if (/^[=+\-@\t\r]/.test(text)) text = `'${text}`;
        if (/[",\n\r]/.test(text)) text = `"${text.replace(/"/g, '""')}"`;
        return text;
    };

    const toast = (message, tone = 'success') => {
        const root = $('#toast-root');
        const node = document.createElement('div');
        node.className = `toast ${tone}`;
        node.innerHTML = `${icon(tone === 'danger' ? 'x' : 'check')}<span>${esc(message)}</span>`;
        root.appendChild(node);
        while (root.children.length > 3) root.firstElementChild.remove();
        setTimeout(() => node.remove(), 3600);
    };

    /* ---------- Estado ---------- */

    function mulberry32(seed) {
        return function random() {
            let t = (seed += 0x6D2B79F5);
            t = Math.imul(t ^ (t >>> 15), t | 1);
            t ^= t + Math.imul(t ^ (t >>> 7), t | 61);
            return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
        };
    }

    function seedState() {
        const random = mulberry32(20261030);
        const pick = (arr) => arr[Math.floor(random() * arr.length)];
        const chance = (p) => random() < p;

        const segments = [
            { id: 's1', name: 'Hoteles y resorts', color: '#C65D3B', description: 'Alianzas del sector hotelero y turístico.' },
            { id: 's2', name: 'Proveedores', color: '#7A8B6F', description: 'Empresas que abastecen o colaboran con la operación.' },
            { id: 's3', name: 'ONG y fundaciones', color: '#D9A441', description: 'Organizaciones con proyectos sociales y culturales.' },
            { id: 's4', name: 'Clientes directos', color: '#8C6A9E', description: 'Personas que compran sin intermediarios.' },
            { id: 's5', name: 'Ferias y eventos', color: '#4F7C8A', description: 'Contactos conocidos en ferias y encuentros.' },
        ];

        const users = [
            { id: 'u1', name: 'Alex Demo', email: 'admin@gestordecontactos.test', role: 'admin' },
            { id: 'u2', name: 'Elena Ng', email: 'editor@gestordecontactos.test', role: 'editor' },
            { id: 'u3', name: 'Lucía Ríos', email: 'lector@gestordecontactos.test', role: 'viewer' },
            { id: 'u4', name: 'Marco Chen', email: 'marco@gestordecontactos.test', role: 'editor' },
            { id: 'u5', name: 'Sofía Arias', email: 'sofia@gestordecontactos.test', role: 'viewer' },
        ];

        const firstNames = ['María', 'Jorge', 'Ana', 'Luis', 'Carmen', 'Diego', 'Valeria', 'Ricardo', 'Paola', 'Andrés', 'Isabel', 'Mauricio', 'Gabriela', 'Felipe', 'Daniela', 'Óscar', 'Camila', 'Rafael', 'Natalia', 'Esteban', 'Lorena', 'Iván', 'Patricia', 'Samuel', 'Mónica', 'Javier', 'Adriana', 'Tomás', 'Rosa', 'Emilio'];
        const lastNames = ['López', 'Ríos', 'Villalaz', 'Gómez', 'Herrera', 'Cárdenas', 'Morales', 'Castillo', 'Navarro', 'Aguilar', 'Batista', 'Domínguez', 'Pineda', 'Quintero', 'Salazar', 'Torres', 'Valdés', 'Vargas', 'Arosemena', 'Bermúdez', 'Cedeño', 'Escobar', 'Guerra', 'Jaén'];
        const companies = ['Hotel Playa', 'Grupo Mar', 'ONG Raíces', 'Café del Valle', 'Distribuidora Istmo', 'Estudio Manglar', 'Finca La Esperanza', 'Hotel Bocas', 'Fundación Semilla', 'Retail Central', 'Marea Coworking', 'Ruta Verde Tours'];
        const cities = [['Panamá', 'Panamá'], ['Colón', 'Colón'], ['David', 'Chiriquí'], ['Chitré', 'Herrera'], ['Las Tablas', 'Los Santos'], ['Santiago', 'Veraguas'], ['Penonomé', 'Coclé'], ['La Chorrera', 'Panamá Oeste'], ['Bocas del Toro', 'Bocas del Toro'], ['Aguadulce', 'Coclé']];
        const sources = ['Feria', 'Referido', 'Web', 'Instagram', 'Evento', 'Importación CSV', 'WhatsApp'];
        const businessTypes = ['Hotel', 'Restaurante', 'Proveedor', 'ONG', 'Retail', 'Turismo', 'Servicios'];
        const genders = ['Femenino', 'Masculino', 'Otro', 'Prefiere no decir'];
        const notesPool = [
            'Prefiere que la contacten por WhatsApp después de las 3 p. m.',
            'Pidió una cotización para el próximo trimestre.',
            'Se conoció en la feria de turismo; interesada en alianzas.',
            'Recomendó a dos personas del sector hotelero.',
            'Hay que enviarle el catálogo actualizado y la lista de precios.',
            'Solo acepta llamadas; revisa el correo con poca frecuencia.',
            'Quedó en confirmar la reunión la próxima semana.',
            'Es contacto clave para la campaña de fin de año.',
            'Le interesa participar en el próximo encuentro de proveedores.',
            'Habla inglés y francés; útil para clientes internacionales.',
        ];

        const contacts = [];
        for (let i = 0; i < 48; i++) {
            const first = firstNames[i % firstNames.length];
            const last = lastNames[(i * 7) % lastNames.length];
            const name = `${first} ${last}`;
            const isBusiness = i % 3 !== 0;
            const [city, province] = cities[Math.floor(random() * cities.length)];
            const company = isBusiness ? companies[Math.floor(random() * companies.length)] : null;

            let birthdate;
            if (i < 6) {
                const day = addDays(new Date(), [0, 3, 8, 14, 21, 29][i]);
                birthdate = `${day.getFullYear() - (28 + (i % 12))}-${String(day.getMonth() + 1).padStart(2, '0')}-${String(day.getDate()).padStart(2, '0')}`;
            } else {
                birthdate = `${1965 + Math.floor(random() * 38)}-${String(1 + Math.floor(random() * 12)).padStart(2, '0')}-${String(1 + Math.floor(random() * 27)).padStart(2, '0')}`;
            }

            const chosenSegments = chance(.72) ? [pick(segments).id] : [];
            if (chance(.28)) {
                const second = pick(segments).id;
                if (!chosenSegments.includes(second)) chosenSegments.push(second);
            }

            const createdAt = addDays(new Date(), -(i * 6 + Math.floor(random() * 5))).toISOString();

            contacts.push({
                id: `c${i + 1}`,
                name,
                email: `${slug(`${first}${last}${i}`)}@example.test`,
                phone: `+507 6${String(10 + (i % 80)).padStart(2, '0')}-${String(1000 + ((i * 137) % 9000)).padStart(4, '0')}`,
                company,
                job_title: isBusiness ? pick(['Gerente general', 'Directora de ventas', 'Analista de operaciones', 'Fundadora', 'Jefe de compras', 'Coordinadora de proyectos']) : null,
                contact_type: isBusiness ? 'B2B' : 'B2C',
                business_type: isBusiness ? pick(businessTypes) : null,
                gender: pick(genders),
                city,
                province,
                country: 'Panamá',
                birthdate,
                source: pick(sources),
                associated_brand: chance(.18) ? pick(['Marca Sol', 'Casa Verde', 'Arena Norte']) : null,
                associated_company: isBusiness && chance(.12) ? pick(companies) : null,
                segments: chosenSegments,
                created_by: pick(['u1', 'u2']),
                created_at: createdAt,
                updated_at: createdAt,
            });
        }

        contacts[0].name = 'María López';
        contacts[0].email = 'maria.lopez@example.test';
        contacts[0].company = 'Hotel Playa';
        contacts[0].company && (contacts[0].business_type = 'Hotel');
        contacts[0].segments = ['s1', 's5'];
        contacts[1].name = 'Jorge Ríos';
        contacts[1].email = 'jorge.rios@example.test';
        contacts[1].company = 'Grupo Mar';
        contacts[1].segments = ['s2'];
        contacts[2].name = 'Ana Villalaz';
        contacts[2].email = 'ana.villalaz@example.test';
        contacts[2].company = 'ONG Raíces';
        contacts[2].segments = ['s3'];
        contacts[2].birthdate = `${new Date().getFullYear() - 34}-${String(new Date().getMonth() + 1).padStart(2, '0')}-${String(new Date().getDate()).padStart(2, '0')}`;

        const notes = [
            { id: 'n1', contact_id: 'c1', user_id: 'u2', body: 'Prefiere las llamadas por la mañana y las cotizaciones por correo.', created_at: addDays(new Date(), -2).toISOString() },
            { id: 'n2', contact_id: 'c1', user_id: 'u1', body: 'Renovó la alianza para la temporada alta. Enviar propuesta en noviembre.', created_at: addDays(new Date(), -9).toISOString() },
            { id: 'n3', contact_id: 'c2', user_id: 'u2', body: 'Entrega los pedidos los martes. Confirmar volumen antes del viernes.', created_at: addDays(new Date(), -1).toISOString() },
            { id: 'n4', contact_id: 'c3', user_id: 'u1', body: 'La fundación busca alianzas para su programa de becas.', created_at: addDays(new Date(), -4).toISOString() },
        ];
        for (let i = 4; i < 18; i++) {
            const contact = contacts[i];
            notes.push({
                id: `n${i + 1}`,
                contact_id: contact.id,
                user_id: pick(['u1', 'u2']),
                body: pick(notesPool),
                created_at: addDays(new Date(), -(i + 1)).toISOString(),
            });
        }

        const imports = [
            { id: 'i1', file_name: 'contactos-feria-turismo.csv', status: 'completed', total: 128, created: 118, duplicates: 7, errors: 3, created_at: addDays(new Date(), -32).toISOString(), mapping: { name: 'Nombre y Apellido', email: 'Correo', phone: 'Teléfono' }, issues: [{ row: 12, type: 'duplicate', message: 'El correo ya estaba registrado.' }, { row: 44, type: 'error', message: 'Fila sin nombre.' }, { row: 87, type: 'error', message: 'Correo con formato inválido.' }] },
            { id: 'i2', file_name: 'proveedores-2026.csv', status: 'completed', total: 46, created: 39, duplicates: 5, errors: 2, created_at: addDays(new Date(), -18).toISOString(), mapping: { name: 'Nombre', email: 'Email', company: 'Empresa' }, issues: [{ row: 8, type: 'duplicate', message: 'El teléfono ya estaba registrado.' }, { row: 31, type: 'error', message: 'Fila sin correo ni teléfono.' }] },
            { id: 'i3', file_name: 'evento-camara-comercio.csv', status: 'completed', total: 310, created: 281, duplicates: 21, errors: 8, created_at: addDays(new Date(), -6).toISOString(), mapping: { name: 'Contacto', email: 'Correo electrónico', phone: 'Celular' }, issues: [{ row: 55, type: 'duplicate', message: 'El correo ya estaba registrado.' }, { row: 199, type: 'error', message: 'Correo con formato inválido.' }] },
            { id: 'i4', file_name: 'base-historica.csv', status: 'failed', total: 0, created: 0, duplicates: 0, errors: 1, created_at: addDays(new Date(), -2).toISOString(), mapping: {}, issues: [{ row: 1, type: 'error', message: 'No se reconoció ninguna columna obligatoria.' }] },
            { id: 'i5', file_name: 'hoteles-boutique.csv', status: 'completed', total: 24, created: 22, duplicates: 2, errors: 0, created_at: addDays(new Date(), -1).toISOString(), mapping: { name: 'Nombre del hotel', email: 'Correo', phone: 'WhatsApp' }, issues: [{ row: 9, type: 'duplicate', message: 'El teléfono ya estaba registrado.' }, { row: 17, type: 'duplicate', message: 'El correo ya estaba registrado.' }] },
        ];

        const activity = [
            { id: 'a1', event: 'imported', description: 'Importó 22 contactos desde hoteles-boutique.csv', subject_type: 'import', subject_id: 'i5', user_id: 'u2', created_at: addDays(new Date(), -1).toISOString() },
            { id: 'a2', event: 'note_added', description: 'Añadió una nota a María López', subject_type: 'contact', subject_id: 'c1', user_id: 'u2', created_at: addDays(new Date(), -2).toISOString() },
            { id: 'a3', event: 'updated', description: 'Actualizó el contacto Jorge Ríos', subject_type: 'contact', subject_id: 'c2', user_id: 'u2', created_at: addDays(new Date(), -3).toISOString() },
            { id: 'a4', event: 'created', description: 'Creó el contacto Ana Villalaz', subject_type: 'contact', subject_id: 'c3', user_id: 'u1', created_at: addDays(new Date(), -4).toISOString() },
            { id: 'a5', event: 'imported', description: 'Importó 281 contactos desde evento-camara-comercio.csv', subject_type: 'import', subject_id: 'i3', user_id: 'u1', created_at: addDays(new Date(), -6).toISOString() },
            { id: 'a6', event: 'created', description: 'Creó el segmento Hoteles y resorts', subject_type: 'segment', subject_id: 's1', user_id: 'u1', created_at: addDays(new Date(), -8).toISOString() },
            { id: 'a7', event: 'note_added', description: 'Añadió una nota a Ana Villalaz', subject_type: 'contact', subject_id: 'c3', user_id: 'u1', created_at: addDays(new Date(), -9).toISOString() },
            { id: 'a8', event: 'deleted', description: 'Eliminó un contacto duplicado de la base', subject_type: 'contact', subject_id: null, user_id: 'u1', created_at: addDays(new Date(), -12).toISOString() },
            { id: 'a9', event: 'login', description: 'Inició sesión en la demo', subject_type: 'user', subject_id: 'u1', user_id: 'u1', created_at: addDays(new Date(), -13).toISOString() },
        ];

        return {
            version: 1,
            contacts,
            segments,
            users,
            notes,
            imports,
            activity,
        };
    }

    function loadState() {
        try {
            const raw = localStorage.getItem(STATE_KEY);
            if (raw) {
                const parsed = JSON.parse(raw);
                if (parsed && parsed.version === 1 && Array.isArray(parsed.contacts)) return parsed;
            }
        } catch (error) {
            console.warn('No se pudo leer localStorage, usando datos en memoria.', error);
        }
        const seeded = seedState();
        saveState(seeded);
        return seeded;
    }

    function saveState(next = state) {
        try {
            localStorage.setItem(STATE_KEY, JSON.stringify(next));
        } catch (error) {
            console.warn('No se pudo guardar en localStorage.', error);
        }
    }

    let state = loadState();

    let session = null;
    try {
        session = JSON.parse(localStorage.getItem(SESSION_KEY) || 'null');
    } catch (error) {
        session = null;
    }
    if (session && !ROLES[session.role]) session = null;

    const ui = {
        view: 'dashboard',
        contactSearch: '',
        contactSegment: '',
        contactType: '',
        contactPage: 1,
        perPage: 10,
        activityFilter: '',
        wizard: null,
        editingContact: null,
        editingSegment: null,
        modal: null,
    };

    const currentUser = () => state.users.find((user) => user.id === ROLES[session?.role || 'viewer'].userId) || state.users[0];
    const canWrite = () => session && session.role !== 'viewer';
    const canDeleteContact = () => session && session.role === 'admin';
    const canManageUsers = () => session && session.role === 'admin';
    const segmentById = (id) => state.segments.find((segment) => segment.id === id);
    const contactById = (id) => state.contacts.find((contact) => contact.id === id);
    const userById = (id) => state.users.find((user) => user.id === id);

    function logActivity(event, description, subjectType = 'contact', subjectId = null) {
        state.activity.unshift({
            id: uid('a'),
            event,
            description,
            subject_type: subjectType,
            subject_id: subjectId,
            user_id: currentUser().id,
            created_at: new Date().toISOString(),
        });
        state.activity = state.activity.slice(0, 80);
    }

    /* ---------- Permisos y sesión ---------- */

    function login(role) {
        if (!ROLES[role]) return;
        session = { role };
        localStorage.setItem(SESSION_KEY, JSON.stringify(session));
        logActivity('login', `Inició sesión como ${ROLES[role].label.toLowerCase()}`, 'user', ROLES[role].userId);
        saveState();
        $('#login-view').hidden = true;
        $('#app-view').hidden = false;
        go(location.hash.replace('#', '') || 'dashboard');
        updateSessionUI();
    }

    function logout() {
        session = null;
        localStorage.removeItem(SESSION_KEY);
        $('#app-view').hidden = true;
        $('#login-view').hidden = false;
        closeModal();
        history.replaceState(null, '', location.pathname);
    }

    function updateSessionUI() {
        if (!session) return;
        const user = currentUser();
        $('#side-avatar').style.setProperty('--tone', colorFor(user.name));
        $('#side-avatar').textContent = initials(user.name);
        $('#side-name').textContent = user.name;
        $('#side-role').textContent = ROLES[session.role].label;
        $('#top-avatar').style.setProperty('--tone', colorFor(user.name));
        $('#top-avatar').textContent = initials(user.name);
        $('#top-role').textContent = ROLES[session.role].short;
        $$('.nav [data-view]').forEach((button) => {
            const restricted = button.dataset.view === 'users' && !canManageUsers();
            button.hidden = restricted;
        });
        $$('.new-contact').forEach((button) => { button.hidden = !canWrite(); });
    }

    /* ---------- Navegación ---------- */

    function go(view) {
        const allowed = ['dashboard', 'contacts', 'segments', 'imports', 'activity', 'users', 'settings'];
        ui.view = allowed.includes(view) ? view : 'dashboard';
        if (ui.view === 'users' && !canManageUsers()) {
            ui.view = 'dashboard';
            toast('Solo la administración puede gestionar usuarios.', 'danger');
        }
        if (location.hash !== `#${ui.view}`) history.replaceState(null, '', `#${ui.view}`);
        closeSidebar();
        render();
    }

    function render() {
        if (!session) return;
        updateSessionUI();
        $$('.nav [data-view]').forEach((button) => button.classList.toggle('active', button.dataset.view === ui.view));
        const labels = { dashboard: 'Panel', contacts: 'Contactos', segments: 'Segmentos', imports: 'Importaciones', activity: 'Actividad', users: 'Usuarios', settings: 'Ajustes' };
        $('#crumb').textContent = labels[ui.view];
        const screens = {
            dashboard: screenDashboard,
            contacts: screenContacts,
            segments: screenSegments,
            imports: screenImports,
            activity: screenActivity,
            users: screenUsers,
            settings: screenSettings,
        };
        $('#screen').innerHTML = screens[ui.view]();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    /* ---------- Panel ---------- */

    function screenDashboard() {
        const total = state.contacts.length;
        const thisMonth = state.contacts.filter((contact) => contact.created_at.slice(0, 7) === todayISO().slice(0, 7)).length;
        const birthdays = state.contacts
            .map((contact) => ({ contact, days: daysUntilBirthday(contact.birthdate) }))
            .filter((entry) => entry.days !== null && entry.days <= 30)
            .sort((a, b) => a.days - b.days);
        const b2b = state.contacts.filter((contact) => contact.contact_type === 'B2B').length;
        const maxSegment = Math.max(1, ...state.segments.map((segment) => state.contacts.filter((contact) => contact.segments.includes(segment.id)).length));
        const hour = new Date().getHours();
        const greeting = hour < 12 ? 'Buenos días' : hour < 19 ? 'Buenas tardes' : 'Buenas noches';

        return `
            <div class="page-head">
                <div>
                    <p class="eyebrow">Panel</p>
                    <h1>${greeting}, ${esc(currentUser().name.split(' ')[0])}.</h1>
                    <p class="muted">Esto es lo que está pasando en tu directorio esta semana.</p>
                </div>
                <div class="head-actions">
                    ${canWrite() ? `<button class="btn" data-action="new-import">${icon('upload')} Importar CSV</button>` : ''}
                    ${canWrite() ? `<button class="btn primary" data-action="new-contact">${icon('plus')} Nuevo contacto</button>` : ''}
                </div>
            </div>

            <div class="card hero-card">
                <div>
                    <h2>${birthdays.length ? `Hay ${birthdays.length} cumpleaños en los próximos 30 días.` : 'Nadie cumple años en los próximos 30 días.'}</h2>
                    <p>${birthdays.length ? `El más cercano es ${esc(birthdays[0].contact.name)}${birthdays[0].days === 0 ? ' hoy' : ` en ${birthdays[0].days} día${birthdays[0].days === 1 ? '' : 's'}`}. Un mensaje a tiempo vale mucho.` : 'Aprovecha para completar fechas de nacimiento y no volver a olvidar una felicitación.'}</p>
                </div>
                <div class="hero-actions">
                    <button class="btn small" data-view="contacts">${icon('users')} Ver directorio</button>
                    ${canWrite() ? `<button class="btn small primary" data-action="new-contact">${icon('plus')} Agregar</button>` : ''}
                </div>
            </div>

            <div class="stats">
                <article class="card stat"><span class="stat-icon clay">${icon('users')}</span><div><small>Contactos</small><strong>${total}</strong><p>${thisMonth} nuevos este mes</p></div></article>
                <article class="card stat"><span class="stat-icon sage">${icon('tag')}</span><div><small>Segmentos</small><strong>${state.segments.length}</strong><p>Con color y equipo</p></div></article>
                <article class="card stat"><span class="stat-icon gold">${icon('cake')}</span><div><small>Cumpleaños</small><strong>${birthdays.length}</strong><p>En los próximos 30 días</p></div></article>
                <article class="card stat"><span class="stat-icon ink">${icon('building')}</span><div><small>Empresas</small><strong>${b2b}</strong><p>${total - b2b} contactos personales</p></div></article>
            </div>

            <div class="grid-2">
                <div class="stack">
                    <section class="card">
                        <div class="section-title"><div><h2>Cumpleaños próximos</h2><small>Un detalle pequeño, un gran recordatorio.</small></div><button class="section-link" data-view="contacts">Directorio ${icon('chevron')}</button></div>
                        <div class="birthday-list">
                            ${birthdays.slice(0, 5).map(({ contact, days }) => `
                                <button class="birthday" data-contact-open="${contact.id}">
                                    ${avatar(contact.name)}
                                    <div><strong>${esc(contact.name)}</strong><small>${esc(contact.company || contact.city || 'Contacto personal')}</small></div>
                                    <span class="when">${days === 0 ? 'Hoy' : `En ${days} d`}</span>
                                </button>
                            `).join('') || `<div class="empty">${icon('cake')}<strong>Sin cumpleaños a la vista</strong><p>Agrega fechas de nacimiento para no olvidarlos.</p></div>`}
                        </div>
                    </section>

                    <section class="card">
                        <div class="section-title"><div><h2>Actividad reciente</h2><small>Quién hizo qué y cuándo.</small></div><button class="section-link" data-view="activity">Ver todo ${icon('chevron')}</button></div>
                        <div class="feed">
                            ${state.activity.slice(0, 6).map((item) => `
                                <div class="feed-item">
                                    <span class="feed-icon">${icon(ICONS[item.event] || 'history')}</span>
                                    <div><p>${esc(item.description)}</p><time>${esc(userById(item.user_id)?.name || 'Alguien')} · ${relativeTime(item.created_at)}</time></div>
                                </div>
                            `).join('')}
                        </div>
                    </section>
                </div>

                <div class="stack">
                    <section class="card">
                        <div class="section-title"><div><h2>Segmentos</h2><small>Dónde está tu gente.</small></div><button class="section-link" data-view="segments">Gestionar ${icon('chevron')}</button></div>
                        <div class="segment-bars">
                            ${state.segments.map((segment) => {
                                const count = state.contacts.filter((contact) => contact.segments.includes(segment.id)).length;
                                return `<div class="segment-bar"><div class="row"><span><i class="swatch" style="background:${segment.color}"></i>${esc(segment.name)}</span><strong>${count}</strong></div><div class="bar"><span style="width:${Math.round((count / maxSegment) * 100)}%;background:${segment.color}"></span></div></div>`;
                            }).join('')}
                        </div>
                    </section>

                    <section class="card">
                        <div class="section-title"><div><h2>Importaciones</h2><small>Lo último que entró.</small></div><button class="section-link" data-view="imports">Historial ${icon('chevron')}</button></div>
                        <div>
                            ${state.imports.slice(0, 3).map((item) => `
                                <div class="import-row">
                                    <span class="import-badge">${icon('doc')}</span>
                                    <div><strong>${esc(item.file_name)}</strong><small>${item.created} creados · ${formatDate(item.created_at.slice(0, 10))}</small></div>
                                    <span class="status ${item.status}">${statusLabel(item.status)}</span>
                                </div>
                            `).join('')}
                        </div>
                    </section>
                </div>
            </div>
        `;
    }

    const statusLabel = (status) => ({ completed: 'Completada', processing: 'Procesando', pending: 'Pendiente', failed: 'Fallida' }[status] || status);

    /* ---------- Contactos ---------- */

    function filteredContacts() {
        const term = normalize(ui.contactSearch);
        return state.contacts.filter((contact) => {
            if (ui.contactSegment && !contact.segments.includes(ui.contactSegment)) return false;
            if (ui.contactType && contact.contact_type !== ui.contactType) return false;
            if (!term) return true;
            return [contact.name, contact.email, contact.phone, contact.company, contact.city].some((value) => normalize(value).includes(term));
        }).sort((a, b) => a.name.localeCompare(b.name, 'es'));
    }

    function contactRows(rows) {
        if (!rows.length) {
            return `<tr><td colspan="6"><div class="empty">${icon('users')}<strong>No encontramos contactos</strong><p>Prueba con otra búsqueda o quita algunos filtros.</p></div></td></tr>`;
        }
        return rows.map((contact) => {
            const tags = contact.segments.map((id) => segmentById(id)).filter(Boolean);
            const days = daysUntilBirthday(contact.birthdate);
            return `
                <tr>
                    <td>
                        <button class="cell-person" data-contact-open="${contact.id}" style="border:0;background:transparent;padding:0;text-align:left;color:inherit">
                            ${avatar(contact.name)}
                            <div><strong>${esc(contact.name)}</strong><small>${esc(contact.job_title || (contact.contact_type === 'B2B' ? 'Contacto B2B' : 'Contacto personal'))}</small></div>
                        </button>
                    </td>
                    <td><div class="cell-muted">${esc(contact.email || '—')}<br>${esc(contact.phone || '—')}</div></td>
                    <td><div>${esc(contact.company || '—')}<br><small class="cell-muted">${esc(contact.city || '')}</small></div></td>
                    <td><div class="tag-list">${tags.map((segment) => `<span class="tag" style="background:${segment.color}1f;color:${segment.color}"><i class="tag-dot" style="background:${segment.color}"></i>${esc(segment.name)}</span>`).join('') || '<span class="cell-muted">Sin segmento</span>'}</div></td>
                    <td><div class="cell-muted">${days === 0 ? '<strong style="color:var(--gold-700)">Hoy</strong>' : days !== null && days <= 30 ? `En ${days} d` : formatDate(contact.birthdate, { day: 'numeric', month: 'short' })}</div></td>
                    <td>
                        <div class="row-actions">
                            <button class="icon-btn" data-contact-open="${contact.id}" aria-label="Ver ${esc(contact.name)}">${icon('eye')}</button>
                            ${canWrite() ? `<button class="icon-btn" data-contact-edit="${contact.id}" aria-label="Editar ${esc(contact.name)}">${icon('edit')}</button>` : ''}
                            ${canDeleteContact() ? `<button class="icon-btn" data-contact-delete="${contact.id}" aria-label="Eliminar ${esc(contact.name)}">${icon('trash')}</button>` : ''}
                        </div>
                    </td>
                </tr>`;
        }).join('');
    }

    function pagination(rows) {
        const pages = Math.max(1, Math.ceil(rows.length / ui.perPage));
        ui.contactPage = Math.min(ui.contactPage, pages);
        const start = (ui.contactPage - 1) * ui.perPage;
        const buttons = [];
        for (let page = 1; page <= pages; page++) {
            if (pages > 7 && page !== 1 && page !== pages && Math.abs(page - ui.contactPage) > 1) {
                if (buttons[buttons.length - 1] !== '…') buttons.push('…');
                continue;
            }
            buttons.push(page);
        }
        return {
            start,
            view: rows.slice(start, start + ui.perPage),
            html: `<div class="pagination"><span>${rows.length} contactos · mostrando ${rows.length ? start + 1 : 0}–${Math.min(start + ui.perPage, rows.length)}</span><div>
                <button class="page-btn" data-page="${ui.contactPage - 1}" ${ui.contactPage === 1 ? 'disabled' : ''} aria-label="Anterior">‹</button>
                ${buttons.map((page) => page === '…' ? '<span class="cell-muted">…</span>' : `<button class="page-btn ${page === ui.contactPage ? 'active' : ''}" data-page="${page}">${page}</button>`).join('')}
                <button class="page-btn" data-page="${ui.contactPage + 1}" ${ui.contactPage === pages ? 'disabled' : ''} aria-label="Siguiente">›</button>
            </div></div>`,
        };
    }

    function screenContacts() {
        const rows = filteredContacts();
        const page = pagination(rows);
        return `
            <div class="page-head">
                <div>
                    <p class="eyebrow">Directorio</p>
                    <h1>Contactos</h1>
                    <p class="muted">${state.contacts.length} personas y organizaciones. Busca, filtra y exporta lo que necesites.</p>
                </div>
                <div class="head-actions">
                    <button class="btn" data-action="export-csv">${icon('download')} Exportar CSV</button>
                    ${canWrite() ? `<button class="btn primary" data-action="new-contact">${icon('plus')} Nuevo contacto</button>` : ''}
                </div>
            </div>

            <div class="toolbar">
                <div class="search">${icon('search')}<input id="contact-search" type="search" placeholder="Buscar por nombre, correo, empresa o ciudad…" value="${esc(ui.contactSearch)}" autocomplete="off"></div>
                <select class="select" id="filter-segment" aria-label="Filtrar por segmento">
                    <option value="">Todos los segmentos</option>
                    ${state.segments.map((segment) => `<option value="${segment.id}" ${ui.contactSegment === segment.id ? 'selected' : ''}>${esc(segment.name)}</option>`).join('')}
                </select>
                <select class="select" id="filter-type" aria-label="Filtrar por tipo">
                    <option value="">B2B y B2C</option>
                    <option value="B2B" ${ui.contactType === 'B2B' ? 'selected' : ''}>Solo B2B</option>
                    <option value="B2C" ${ui.contactType === 'B2C' ? 'selected' : ''}>Solo B2C</option>
                </select>
            </div>

            ${(ui.contactSearch || ui.contactSegment || ui.contactType) ? `<div class="chips">
                ${ui.contactSearch ? `<span class="chip">“${esc(ui.contactSearch)}” <button data-clear="search" aria-label="Quitar búsqueda">${icon('x')}</button></span>` : ''}
                ${ui.contactSegment ? `<span class="chip">${esc(segmentById(ui.contactSegment)?.name || '')} <button data-clear="segment" aria-label="Quitar filtro">${icon('x')}</button></span>` : ''}
                ${ui.contactType ? `<span class="chip">${esc(ui.contactType)} <button data-clear="type" aria-label="Quitar filtro">${icon('x')}</button></span>` : ''}
                <button class="section-link" data-clear="all">Limpiar todo</button>
            </div>` : ''}

            <div class="card">
                <div class="table-wrap">
                    <table class="data contacts-table">
                        <thead><tr><th>Persona</th><th>Contacto</th><th>Empresa</th><th>Segmentos</th><th>Cumpleaños</th><th></th></tr></thead>
                        <tbody>${contactRows(page.view)}</tbody>
                    </table>
                </div>
                ${pagination(rows).html}
            </div>
        `;
    }

    function contactModal(contactId) {
        const contact = contactById(contactId);
        if (!contact) return;
        const tags = contact.segments.map((id) => segmentById(id)).filter(Boolean);
        const notes = state.notes.filter((note) => note.contact_id === contact.id).sort((a, b) => b.created_at.localeCompare(a.created_at));
        const history = state.activity.filter((item) => item.subject_type === 'contact' && item.subject_id === contact.id).slice(0, 6);
        const canNote = canWrite();

        openModal(`
            <div class="modal wide">
                <div class="modal-head">
                    <div><h2>${esc(contact.name)}</h2><p>${esc(contact.company || (contact.contact_type === 'B2B' ? 'Contacto B2B' : 'Contacto personal'))}${contact.city ? ` · ${esc(contact.city)}` : ''}</p></div>
                    <button class="icon-btn" data-action="close-modal" aria-label="Cerrar">${icon('x')}</button>
                </div>
                <div class="detail-grid" style="padding:1rem 1.3rem 1.3rem">
                    <div class="stack">
                        <div class="card profile-hero">
                            ${avatar(contact.name)}
                            <div style="flex:1;min-width:0">
                                <h2>${esc(contact.name)}</h2>
                                <p>${esc(contact.email || 'Sin correo')} · ${esc(contact.phone || 'Sin teléfono')}</p>
                                <div class="tag-list" style="margin-top:.45rem">${tags.map((segment) => `<span class="tag" style="background:${segment.color}1f;color:${segment.color}"><i class="tag-dot" style="background:${segment.color}"></i>${esc(segment.name)}</span>`).join('') || '<span class="cell-muted">Sin segmento</span>'}</div>
                            </div>
                            <div style="display:grid;gap:.4rem;justify-items:end">
                                ${contact.email ? `<a class="btn small" href="mailto:${esc(contact.email)}">${icon('mail')} Escribir</a>` : ''}
                                ${contact.phone ? `<a class="btn small" href="tel:${esc(contact.phone.replace(/\s+/g, ''))}">${icon('phone')} Llamar</a>` : ''}
                            </div>
                        </div>

                        <div class="card">
                            <div class="section-title"><div><h2>Ficha</h2><small>Información guardada del contacto.</small></div></div>
                            <dl class="dl">
                                <div><dt>Empresa</dt><dd>${esc(contact.company || '—')}</dd></div>
                                <div><dt>Cargo</dt><dd>${esc(contact.job_title || '—')}</dd></div>
                                <div><dt>Tipo</dt><dd>${contact.contact_type === 'B2B' ? 'B2B · Empresa' : contact.contact_type === 'B2C' ? 'B2C · Persona' : '—'}</dd></div>
                                <div><dt>Tipo de negocio</dt><dd>${esc(contact.business_type || '—')}</dd></div>
                                <div><dt>Cumpleaños</dt><dd>${formatDate(contact.birthdate, { day: 'numeric', month: 'long' })}${contact.birthdate ? ` · ${ageOf(contact.birthdate) + 1} años` : ''}</dd></div>
                                <div><dt>Género</dt><dd>${esc(contact.gender || '—')}</dd></div>
                                <div><dt>Ubicación</dt><dd>${esc([contact.city, contact.province, contact.country].filter(Boolean).join(', ') || '—')}</dd></div>
                                <div><dt>Origen</dt><dd>${esc(contact.source || '—')}</dd></div>
                                <div><dt>Marca asociada</dt><dd>${esc(contact.associated_brand || '—')}</dd></div>
                                <div><dt>Empresa asociada</dt><dd>${esc(contact.associated_company || '—')}</dd></div>
                            </dl>
                        </div>

                        <div class="card">
                            <div class="section-title"><div><h2>Notas</h2><small>Contexto que no cabe en un campo.</small></div></div>
                            ${canNote ? `<form class="note-box" data-form="add-note" data-contact="${contact.id}" style="border-top:0;padding-top:0">
                                <textarea name="body" placeholder="Apunta preferencias, acuerdos o el próximo paso…" required minlength="2" maxlength="2000"></textarea>
                                <div style="display:flex;justify-content:flex-end;margin-top:.45rem"><button class="btn primary small" type="submit">${icon('plus')} Añadir nota</button></div>
                            </form>` : ''}
                            <div class="notes">
                                ${notes.map((note) => `<div class="note">${esc(note.body)}
                                    ${(session.role === 'admin' || (canWrite() && note.user_id === currentUser().id)) ? `<button class="note-del" data-note-delete="${note.id}" aria-label="Eliminar nota">${icon('trash')}</button>` : ''}
                                    <small>${esc(userById(note.user_id)?.name || 'Alguien')} · ${relativeTime(note.created_at)}</small>
                                </div>`).join('') || `<p class="cell-muted" style="font-size:.8rem">Todavía no hay notas.</p>`}
                            </div>
                        </div>
                    </div>

                    <div class="stack">
                        <div class="card">
                            <div class="section-title"><div><h2>Historial</h2><small>Cambios sobre este contacto.</small></div></div>
                            <div class="feed">
                                ${history.map((item) => `<div class="feed-item"><span class="feed-icon">${icon(ICONS[item.event] || 'history')}</span><div><p>${esc(item.description)}</p><time>${esc(userById(item.user_id)?.name || 'Alguien')} · ${relativeTime(item.created_at)}</time></div></div>`).join('') || `<p class="cell-muted" style="font-size:.8rem;padding:.3rem 0 1rem">Sin movimientos registrados.</p>`}
                            </div>
                        </div>
                        ${(canWrite() || canDeleteContact()) ? `<div class="card card-pad" style="display:flex;gap:.5rem;flex-wrap:wrap">
                            ${canWrite() ? `<button class="btn" data-contact-edit="${contact.id}">${icon('edit')} Editar</button>` : ''}
                            ${canDeleteContact() ? `<button class="btn danger" data-contact-delete="${contact.id}">${icon('trash')} Eliminar</button>` : ''}
                        </div>` : `<div class="card card-pad" style="font-size:.78rem;color:var(--muted);line-height:1.55">${icon('eye')} Estás en modo lectura: puedes consultar y exportar, pero no modificar la ficha.</div>`}
                    </div>
                </div>
            </div>
        `);
    }

    function contactForm(contact = null) {
        const editing = Boolean(contact);
        const c = contact || { country: 'Panamá', segments: [] };
        const selectedSegments = new Set(c.segments || []);
        openModal(`
            <form class="modal wide" data-form="contact" data-id="${editing ? contact.id : ''}">
                <div class="modal-head">
                    <div><h2>${editing ? 'Editar contacto' : 'Nuevo contacto'}</h2><p>${editing ? 'Actualiza la ficha sin perder el historial.' : 'Solo el nombre y un canal de contacto son obligatorios.'}</p></div>
                    <button type="button" class="icon-btn" data-action="close-modal" aria-label="Cerrar">${icon('x')}</button>
                </div>
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="field full"><label for="f-name">Nombre completo <span style="color:var(--clay-600)">*</span></label><input id="f-name" name="name" required maxlength="150" value="${esc(c.name || '')}" placeholder="María López"></div>
                        <div class="field"><label for="f-email">Correo</label><input id="f-email" name="email" type="email" maxlength="190" value="${esc(c.email || '')}" placeholder="maria@ejemplo.com"></div>
                        <div class="field"><label for="f-phone">Teléfono</label><input id="f-phone" name="phone" maxlength="40" value="${esc(c.phone || '')}" placeholder="+507 6000-0000"></div>
                        <div class="field"><label for="f-company">Empresa</label><input id="f-company" name="company" maxlength="150" value="${esc(c.company || '')}"></div>
                        <div class="field"><label for="f-job">Cargo</label><input id="f-job" name="job_title" maxlength="120" value="${esc(c.job_title || '')}"></div>

                        <div class="section-sep">Perfil comercial</div>
                        <div class="field"><label for="f-type">Tipo de cliente</label><select id="f-type" name="contact_type"><option value="">Sin definir</option><option value="B2B" ${c.contact_type === 'B2B' ? 'selected' : ''}>B2B · Empresa</option><option value="B2C" ${c.contact_type === 'B2C' ? 'selected' : ''}>B2C · Persona</option></select></div>
                        <div class="field"><label for="f-business">Tipo de negocio</label><input id="f-business" name="business_type" maxlength="80" value="${esc(c.business_type || '')}" placeholder="Hotel, Proveedor, ONG…"></div>
                        <div class="field"><label for="f-gender">Género</label><select id="f-gender" name="gender"><option value="">Sin definir</option>${['Femenino', 'Masculino', 'Otro', 'Prefiere no decir'].map((item) => `<option ${c.gender === item ? 'selected' : ''}>${item}</option>`).join('')}</select></div>
                        <div class="field"><label for="f-source">Origen</label><input id="f-source" name="source" maxlength="80" value="${esc(c.source || '')}" placeholder="Feria, Referido, Web…"></div>

                        <div class="section-sep">Ubicación y fecha</div>
                        <div class="field"><label for="f-city">Ciudad</label><input id="f-city" name="city" maxlength="80" value="${esc(c.city || '')}"></div>
                        <div class="field"><label for="f-province">Provincia</label><input id="f-province" name="province" maxlength="80" value="${esc(c.province || '')}"></div>
                        <div class="field"><label for="f-country">País</label><input id="f-country" name="country" maxlength="80" value="${esc(c.country || '')}"></div>
                        <div class="field"><label for="f-birthdate">Fecha de nacimiento</label><input id="f-birthdate" name="birthdate" type="date" value="${esc(c.birthdate || '')}"></div>

                        <div class="section-sep">Asociaciones</div>
                        <div class="field"><label for="f-brand">Marca asociada</label><input id="f-brand" name="associated_brand" maxlength="120" value="${esc(c.associated_brand || '')}"></div>
                        <div class="field"><label for="f-company-assoc">Empresa asociada</label><input id="f-company-assoc" name="associated_company" maxlength="150" value="${esc(c.associated_company || '')}"></div>

                        <div class="field full"><label>Segmentos</label><div class="check-list">${state.segments.map((segment) => `<label class="check-chip"><input type="checkbox" name="segments" value="${segment.id}" ${selectedSegments.has(segment.id) ? 'checked' : ''}><i class="swatch" style="background:${segment.color}"></i>${esc(segment.name)}</label>`).join('')}</div></div>
                        <div class="field full" id="contact-error" hidden><p class="error-text"></p></div>
                    </div>
                </div>
                <div class="modal-foot">
                    ${editing && canDeleteContact() ? `<button type="button" class="btn danger" data-contact-delete="${contact.id}">${icon('trash')} Eliminar</button><span class="spacer"></span>` : '<span class="spacer"></span>'}
                    <button type="button" class="btn ghost" data-action="close-modal">Cancelar</button>
                    <button type="submit" class="btn primary">${icon('check')} ${editing ? 'Guardar cambios' : 'Crear contacto'}</button>
                </div>
            </form>
        `);
    }

    function saveContact(form) {
        const data = new FormData(form);
        const id = form.dataset.id || null;
        const name = String(data.get('name') || '').trim();
        const email = String(data.get('email') || '').trim().toLowerCase();
        const phone = String(data.get('phone') || '').trim();
        const errorBox = $('#contact-error', form);
        const showError = (message) => {
            errorBox.hidden = false;
            $('.error-text', errorBox).textContent = message;
        };

        if (!name) return showError('El nombre es obligatorio.');
        if (!email && !phone) return showError('Añade al menos un correo o un teléfono.');
        if (email && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) return showError('El correo no tiene un formato válido.');

        const duplicate = state.contacts.find((contact) => contact.id !== id && ((email && contact.email?.toLowerCase() === email) || (phone && contact.phone === phone)));
        if (duplicate) return showError(`Ya existe un contacto con ese ${duplicate.email?.toLowerCase() === email ? 'correo' : 'teléfono'}: ${duplicate.name}.`);

        const payload = {
            name,
            email: email || null,
            phone: phone || null,
            company: String(data.get('company') || '').trim() || null,
            job_title: String(data.get('job_title') || '').trim() || null,
            contact_type: String(data.get('contact_type') || '') || null,
            business_type: String(data.get('business_type') || '').trim() || null,
            gender: String(data.get('gender') || '') || null,
            city: String(data.get('city') || '').trim() || null,
            province: String(data.get('province') || '').trim() || null,
            country: String(data.get('country') || '').trim() || null,
            birthdate: data.get('birthdate') || null,
            source: String(data.get('source') || '').trim() || null,
            associated_brand: String(data.get('associated_brand') || '').trim() || null,
            associated_company: String(data.get('associated_company') || '').trim() || null,
            segments: data.getAll('segments'),
        };

        if (id) {
            const contact = contactById(id);
            Object.assign(contact, payload, { updated_at: new Date().toISOString() });
            logActivity('updated', `Actualizó el contacto ${contact.name}`, 'contact', contact.id);
            toast('Contacto actualizado.');
        } else {
            const contact = {
                id: uid('c'),
                ...payload,
                created_by: currentUser().id,
                created_at: new Date().toISOString(),
                updated_at: new Date().toISOString(),
            };
            state.contacts.unshift(contact);
            logActivity('created', `Creó el contacto ${contact.name}`, 'contact', contact.id);
            toast('Contacto creado.');
        }

        saveState();
        closeModal();
        render();
    }

    function deleteContact(id) {
        const contact = contactById(id);
        if (!contact || !canDeleteContact()) return;
        confirmModal('Eliminar contacto', `¿Seguro que quieres eliminar a ${esc(contact.name)}? Se conservarán las notas en el historial de la demo, pero el contacto saldrá del directorio.`, () => {
            state.contacts = state.contacts.filter((item) => item.id !== id);
            state.notes = state.notes.filter((note) => note.contact_id !== id);
            logActivity('deleted', `Eliminó el contacto ${contact.name}`, 'contact', contact.id);
            saveState();
            closeModal();
            render();
            toast('Contacto eliminado.');
        });
    }

    /* ---------- Segmentos ---------- */

    function screenSegments() {
        return `
            <div class="page-head">
                <div>
                    <p class="eyebrow">Organización</p>
                    <h1>Segmentos</h1>
                    <p class="muted">Agrupa contactos por afinidad, sector o campaña. Un contacto puede estar en varios segmentos.</p>
                </div>
                ${canWrite() ? `<div class="head-actions"><button class="btn primary" data-action="new-segment">${icon('plus')} Nuevo segmento</button></div>` : ''}
            </div>
            <div class="segments-grid">
                ${state.segments.map((segment) => {
                    const count = state.contacts.filter((contact) => contact.segments.includes(segment.id)).length;
                    return `<article class="card segment-card" style="--tone:${segment.color}">
                        <div class="segment-card-head"><div><h3>${esc(segment.name)}</h3></div><span class="tag" style="background:${segment.color}1f;color:${segment.color}"><i class="tag-dot" style="background:${segment.color}"></i>${count} contactos</span></div>
                        <p class="desc">${esc(segment.description || 'Sin descripción.')}</p>
                        <div class="segment-meta">
                            <span class="segment-count">${icon('users')} ${count} ${count === 1 ? 'persona' : 'personas'}</span>
                            <div class="row-actions">
                                <button class="btn small" data-segment-view="${segment.id}">${icon('eye')} Ver</button>
                                ${canWrite() ? `<button class="icon-btn" data-segment-edit="${segment.id}" aria-label="Editar ${esc(segment.name)}">${icon('edit')}</button>` : ''}
                                ${canWrite() ? `<button class="icon-btn" data-segment-delete="${segment.id}" aria-label="Eliminar ${esc(segment.name)}">${icon('trash')}</button>` : ''}
                            </div>
                        </div>
                    </article>`;
                }).join('')}
            </div>
        `;
    }

    function segmentForm(segment = null) {
        const editing = Boolean(segment);
        const tone = segment?.color || SEGMENT_COLORS[0];
        openModal(`
            <form class="modal" data-form="segment" data-id="${editing ? segment.id : ''}">
                <div class="modal-head"><div><h2>${editing ? 'Editar segmento' : 'Nuevo segmento'}</h2><p>Un nombre claro y un color cálido ayudan a reconocerlo de un vistazo.</p></div><button type="button" class="icon-btn" data-action="close-modal" aria-label="Cerrar">${icon('x')}</button></div>
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="field full"><label for="s-name">Nombre <span style="color:var(--clay-600)">*</span></label><input id="s-name" name="name" required maxlength="60" value="${esc(segment?.name || '')}" placeholder="Hoteles boutique"></div>
                        <div class="field full"><label for="s-desc">Descripción</label><textarea id="s-desc" name="description" maxlength="180" placeholder="¿Qué tienen en común estas personas?">${esc(segment?.description || '')}</textarea></div>
                        <div class="field full"><label>Color</label><input type="hidden" name="color" value="${tone}"><div class="palette">${SEGMENT_COLORS.map((color) => `<button type="button" class="color-dot ${color === tone ? 'active' : ''}" style="background:${color}" data-color="${color}" aria-label="Elegir color ${color}"></button>`).join('')}</div></div>
                        <div class="field full" id="segment-error" hidden><p class="error-text"></p></div>
                    </div>
                </div>
                <div class="modal-foot"><span class="spacer"></span><button type="button" class="btn ghost" data-action="close-modal">Cancelar</button><button type="submit" class="btn primary">${icon('check')} Guardar</button></div>
            </form>
        `);
    }

    function saveSegment(form) {
        const data = new FormData(form);
        const id = form.dataset.id || null;
        const name = String(data.get('name') || '').trim();
        const color = String(data.get('color') || SEGMENT_COLORS[0]);
        const description = String(data.get('description') || '').trim() || null;
        const errorBox = $('#segment-error', form);
        const showError = (message) => { errorBox.hidden = false; $('.error-text', errorBox).textContent = message; };

        if (!name) return showError('El nombre es obligatorio.');
        if (state.segments.some((segment) => segment.id !== id && normalize(segment.name) === normalize(name))) return showError('Ya existe un segmento con ese nombre.');

        if (id) {
            Object.assign(segmentById(id), { name, color, description });
            logActivity('updated', `Actualizó el segmento ${name}`, 'segment', id);
            toast('Segmento actualizado.');
        } else {
            const segment = { id: uid('s'), name, color, description };
            state.segments.push(segment);
            logActivity('created', `Creó el segmento ${name}`, 'segment', segment.id);
            toast('Segmento creado.');
        }
        saveState();
        closeModal();
        render();
    }

    function deleteSegment(id) {
        const segment = segmentById(id);
        if (!segment || !canWrite()) return;
        confirmModal('Eliminar segmento', `¿Eliminar “${esc(segment.name)}”? Los contactos se conservan; solo dejarán de pertenecer a este segmento.`, () => {
            state.segments = state.segments.filter((item) => item.id !== id);
            state.contacts.forEach((contact) => { contact.segments = contact.segments.filter((sid) => sid !== id); });
            logActivity('deleted', `Eliminó el segmento ${segment.name}`, 'segment', id);
            saveState();
            closeModal();
            render();
            toast('Segmento eliminado. Los contactos se conservaron.');
        });
    }

    /* ---------- Importaciones ---------- */

    function screenImports() {
        const stats = {
            completed: state.imports.filter((item) => item.status === 'completed').length,
            created: state.imports.reduce((sum, item) => sum + (item.created || 0), 0),
            duplicates: state.imports.reduce((sum, item) => sum + (item.duplicates || 0), 0),
            errors: state.imports.reduce((sum, item) => sum + (item.errors || 0), 0),
        };
        return `
            <div class="page-head">
                <div>
                    <p class="eyebrow">Datos</p>
                    <h1>Importaciones</h1>
                    <p class="muted">Sube un CSV, empareja las columnas y revisa qué pasó con cada fila antes de confirmar.</p>
                </div>
                <div class="head-actions">
                    <button class="btn" data-action="download-sample">${icon('download')} CSV de ejemplo</button>
                    ${canWrite() ? `<button class="btn primary" data-action="new-import">${icon('upload')} Importar CSV</button>` : ''}
                </div>
            </div>

            <div class="stats">
                <article class="card stat"><span class="stat-icon sage">${icon('check')}</span><div><small>Completadas</small><strong>${stats.completed}</strong><p>Importaciones cerradas</p></div></article>
                <article class="card stat"><span class="stat-icon clay">${icon('users')}</span><div><small>Contactos creados</small><strong>${stats.created}</strong><p>Suma histórica</p></div></article>
                <article class="card stat"><span class="stat-icon gold">${icon('shield')}</span><div><small>Duplicados omitidos</small><strong>${stats.duplicates}</strong><p>Sin tocar tu base</p></div></article>
                <article class="card stat"><span class="stat-icon ink">${icon('doc')}</span><div><small>Errores</small><strong>${stats.errors}</strong><p>Filas que necesitaron atención</p></div></article>
            </div>

            <div class="card">
                <div class="section-title"><div><h2>Historial</h2><small>${state.imports.length} archivos procesados en esta demo.</small></div></div>
                ${state.imports.map((item) => `
                    <div class="import-row">
                        <span class="import-badge">${icon('doc')}</span>
                        <div><strong>${esc(item.file_name)}</strong><small>${formatDate(item.created_at.slice(0, 10))} · ${item.total} filas · ${item.created} creados · ${item.duplicates} duplicados · ${item.errors} errores</small></div>
                        <span class="status ${item.status}">${statusLabel(item.status)}</span>
                        <button class="icon-btn" data-import-view="${item.id}" aria-label="Ver informe">${icon('eye')}</button>
                    </div>
                `).join('')}
            </div>
        `;
    }

    function importModal() {
        ui.wizard = { step: 1, fileName: '', headers: [], rows: [], mapping: {}, analysis: null };
        openModal(wizardHtml());
    }

    function wizardHtml() {
        const w = ui.wizard;
        if (!w) return '';
        const steps = [
            { n: 1, label: 'Archivo' },
            { n: 2, label: 'Mapeo' },
            { n: 3, label: 'Revisión' },
        ];
        let body = '';

        if (w.step === 1) {
            body = `
                <div class="dropzone" id="dropzone">
                    ${icon('upload')}
                    <strong>Arrastra tu CSV aquí</strong>
                    <p>o elige un archivo del equipo. Acepta coma, punto y coma o tabulador.</p>
                    <button type="button" class="btn primary" data-action="pick-csv">Elegir archivo</button>
                </div>
                <p class="cell-muted" style="font-size:.72rem;margin-top:.7rem">Los datos no salen de tu navegador. Puedes descargar el CSV de ejemplo para probar.</p>
            `;
        } else if (w.step === 2) {
            body = `
                <div style="display:flex;align-items:center;justify-content:space-between;gap:.6rem;flex-wrap:wrap;margin-bottom:.7rem">
                    <span class="chip">${icon('doc')} ${esc(w.fileName)} · ${w.rows.length} filas</span>
                    <button class="section-link" data-action="wizard-reset">Cambiar archivo</button>
                </div>
                <div class="map-grid">
                    ${FIELDS.map((field) => `<div class="map-field"><label for="map-${field.key}">${field.label}${field.required ? ' <span class="req">*</span>' : ''}</label><select data-map="${field.key}" id="map-${field.key}"><option value="">— Sin usar —</option>${w.headers.map((header) => `<option value="${esc(header)}" ${w.mapping[field.key] === header ? 'selected' : ''}>${esc(header)}</option>`).join('')}</select></div>`).join('')}
                </div>
                <p style="font-size:.72rem;color:var(--muted);margin-top:.6rem">El nombre es obligatorio. Además, cada fila necesita correo o teléfono.</p>
                <div class="preview-table-wrap" style="overflow-x:auto">
                    <table class="preview-table"><thead><tr>${w.headers.slice(0, 6).map((header) => `<th>${esc(header)}</th>`).join('')}</tr></thead>
                    <tbody>${w.rows.slice(0, 5).map((row) => `<tr>${w.headers.slice(0, 6).map((header, index) => `<td>${esc(row[index] || '')}</td>`).join('')}</tr>`).join('')}</tbody></table>
                </div>
            `;
        } else {
            const a = w.analysis;
            body = `
                <div class="count-cards">
                    <div class="count-card"><small>Filas</small><strong>${a.total}</strong></div>
                    <div class="count-card ok"><small>Listas</small><strong>${a.ready.length}</strong></div>
                    <div class="count-card warn"><small>Duplicadas</small><strong>${a.duplicates}</strong></div>
                    <div class="count-card bad"><small>Errores</small><strong>${a.errors}</strong></div>
                </div>
                <p style="font-size:.8rem;color:var(--muted);margin-bottom:.7rem">Se crearán <strong style="color:var(--text)">${a.ready.length} contactos</strong> y se omitirán ${a.duplicates + a.errors} filas. El informe quedará en el historial.</p>
                <div class="issues">
                    ${a.issues.length ? a.issues.map((issue) => `<div class="issue"><span class="n">Fila ${issue.row}</span><span class="type ${issue.type}">${issue.type === 'duplicate' ? 'Duplicada' : issue.type === 'warning' ? 'Aviso' : 'Error'}</span><span>${esc(issue.message)}</span></div>`).join('') : '<div class="issue"><span>A todas las filas les fue bien. Nada que reportar.</span></div>'}
                </div>
            `;
        }

        const foot = w.step === 1
            ? `<span class="spacer"></span><button type="button" class="btn ghost" data-action="close-modal">Cancelar</button>`
            : w.step === 2
                ? `<button type="button" class="btn ghost" data-action="wizard-back">Atrás</button><span class="spacer"></span><button type="button" class="btn primary" data-action="wizard-analyze">Analizar filas ${icon('arrow-right')}</button>`
                : `<button type="button" class="btn ghost" data-action="wizard-back">Atrás</button><span class="spacer"></span><button type="button" class="btn primary" data-action="wizard-confirm" ${w.analysis.ready.length ? '' : 'disabled'}>Confirmar importación ${icon('check')}</button>`;

        return `
            <div class="modal wide">
                <div class="modal-head"><div><h2>Importar contactos</h2><p>Vista previa, emparejamiento y reporte por fila.</p></div><button type="button" class="icon-btn" data-action="close-modal" aria-label="Cerrar">${icon('x')}</button></div>
                <div class="modal-body">
                    <div class="steps">
                        ${steps.map((step, index) => `<div class="step ${w.step === step.n ? 'active' : ''} ${w.step > step.n ? 'done' : ''}"><i>${w.step > step.n ? '✓' : step.n}</i>${step.label}</div>${index < steps.length - 1 ? '<span class="step-line"></span>' : ''}`).join('')}
                    </div>
                    ${body}
                </div>
                <div class="modal-foot">${foot}</div>
            </div>
        `;
    }

    function refreshWizard() {
        if (!ui.wizard) return;
        const modal = $('#modal-root .modal');
        if (modal) modal.outerHTML = wizardHtml();
    }

    function parseCsv(text) {
        const clean = String(text).replace(/^\uFEFF/, '');
        const firstLine = clean.split(/\r?\n/)[0] || '';
        const counts = { ',': 0, ';': 0, '\t': 0, '|': 0 };
        let inQuotes = false;
        for (const char of firstLine) {
            if (char === '"') inQuotes = !inQuotes;
            if (!inQuotes && counts[char] !== undefined) counts[char]++;
        }
        const delimiter = Object.keys(counts).sort((a, b) => counts[b] - counts[a])[0] || ',';

        const rows = [];
        let row = [];
        let field = '';
        let quoted = false;
        for (let i = 0; i < clean.length; i++) {
            const char = clean[i];
            if (quoted) {
                if (char === '"' && clean[i + 1] === '"') { field += '"'; i++; }
                else if (char === '"') quoted = false;
                else field += char;
            } else if (char === '"') quoted = true;
            else if (char === delimiter) { row.push(field.trim()); field = ''; }
            else if (char === '\n') { row.push(field.trim()); rows.push(row); row = []; field = ''; }
            else if (char !== '\r') field += char;
        }
        if (field !== '' || row.length) { row.push(field.trim()); rows.push(row); }
        const cleaned = rows.filter((line) => line.some((value) => value !== ''));
        const headers = (cleaned.shift() || []).map((header, index) => header || `Columna ${index + 1}`);
        return { headers, rows: cleaned, delimiter };
    }

    function autoMap(headers) {
        const used = new Set();
        const mapping = {};
        for (const field of FIELDS) {
            const normalizedHeader = headers.map((header) => normalize(header));
            let index = normalizedHeader.findIndex((header, i) => !used.has(i) && field.aliases.some((alias) => header === normalize(alias)));
            if (index === -1) index = normalizedHeader.findIndex((header, i) => !used.has(i) && field.aliases.some((alias) => header.includes(normalize(alias))));
            if (index !== -1) {
                mapping[field.key] = headers[index];
                used.add(index);
            }
        }
        return mapping;
    }

    function analyzeWizard() {
        const w = ui.wizard;
        const mapping = {};
        $$('[data-map]').forEach((select) => { mapping[select.dataset.map] = select.value || ''; });
        w.mapping = mapping;
        if (!mapping.name) {
            toast('Empareja la columna que contiene el nombre.', 'danger');
            return;
        }
        if (!mapping.email && !mapping.phone) {
            toast('Empareja al menos una columna de correo o teléfono.', 'danger');
            return;
        }

        const headerIndex = Object.fromEntries(w.headers.map((header, index) => [header, index]));
        const emails = new Map();
        const phones = new Map();
        state.contacts.forEach((contact) => {
            if (contact.email) emails.set(contact.email.toLowerCase(), contact.name);
            if (contact.phone) phones.set(digits(contact.phone), contact.name);
        });

        const ready = [];
        const issues = [];
        let duplicates = 0;
        let errors = 0;

        w.rows.forEach((row, index) => {
            const rowNumber = index + 2;
            const get = (key) => (mapping[key] ? String(row[headerIndex[mapping[key]]] ?? '').trim() : '');
            const name = get('name');
            if (!name) { errors++; issues.push({ row: rowNumber, type: 'error', message: 'La fila no tiene nombre.' }); return; }

            const data = {
                name,
                email: get('email').toLowerCase() || null,
                phone: get('phone') || null,
                company: get('company') || null,
                job_title: get('job_title') || null,
                contact_type: /b2b/i.test(get('contact_type')) ? 'B2B' : /b2c/i.test(get('contact_type')) ? 'B2C' : null,
                business_type: get('business_type') || null,
                gender: get('gender') || null,
                city: get('city') || null,
                province: get('province') || null,
                country: get('country') || 'Panamá',
                birthdate: null,
                source: get('source') || 'Importación CSV',
                associated_brand: get('associated_brand') || null,
                associated_company: get('associated_company') || null,
                segments: [],
            };

            const rawDate = get('birthdate');
            if (rawDate) {
                data.birthdate = parseDateInput(rawDate);
                if (!data.birthdate) issues.push({ row: rowNumber, type: 'warning', message: `La fecha “${rawDate}” no se pudo interpretar y se omitió.` });
            }

            if (data.email && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(data.email)) {
                errors++; issues.push({ row: rowNumber, type: 'error', message: 'El correo no tiene un formato válido.' }); return;
            }
            if (!data.email && !data.phone) {
                errors++; issues.push({ row: rowNumber, type: 'error', message: 'La fila no tiene correo ni teléfono.' }); return;
            }

            const emailKey = data.email?.toLowerCase();
            const phoneKey = digits(data.phone);
            if ((emailKey && emails.has(emailKey)) || (phoneKey && phoneKey.length >= 7 && phones.has(phoneKey))) {
                duplicates++;
                issues.push({ row: rowNumber, type: 'duplicate', message: `Ya existe un contacto con ese ${emailKey && emails.has(emailKey) ? 'correo' : 'teléfono'}.` });
                return;
            }

            if (emailKey) emails.set(emailKey, name);
            if (phoneKey) phones.set(phoneKey, name);
            ready.push({ row: rowNumber, data });
        });

        w.analysis = { total: w.rows.length, ready, duplicates, errors, issues: issues.slice(0, 60) };
        w.step = 3;
        refreshWizard();
    }

    function confirmImport() {
        const w = ui.wizard;
        if (!w?.analysis?.ready.length) return;
        const now = new Date().toISOString();
        w.analysis.ready.forEach(({ data }) => {
            state.contacts.unshift({
                id: uid('c'),
                ...data,
                created_by: currentUser().id,
                created_at: now,
                updated_at: now,
            });
        });
        const importRecord = {
            id: uid('i'),
            file_name: w.fileName,
            status: 'completed',
            total: w.analysis.total,
            created: w.analysis.ready.length,
            duplicates: w.analysis.duplicates,
            errors: w.analysis.errors,
            created_at: now,
            mapping: w.mapping,
            issues: w.analysis.issues,
        };
        state.imports.unshift(importRecord);
        logActivity('imported', `Importó ${importRecord.created} contactos desde ${w.fileName}`, 'import', importRecord.id);
        saveState();
        closeModal();
        ui.wizard = null;
        ui.view = 'imports';
        render();
        toast(`Importación completada: ${importRecord.created} contactos creados.`);
    }

    function openImportReport(id) {
        const item = state.imports.find((record) => record.id === id);
        if (!item) return;
        openModal(`
            <div class="modal">
                <div class="modal-head"><div><h2>${esc(item.file_name)}</h2><p>${formatDate(item.created_at.slice(0, 10))} · <span class="status ${item.status}">${statusLabel(item.status)}</span></p></div><button class="icon-btn" data-action="close-modal" aria-label="Cerrar">${icon('x')}</button></div>
                <div class="modal-body">
                    <div class="count-cards">
                        <div class="count-card"><small>Filas</small><strong>${item.total}</strong></div>
                        <div class="count-card ok"><small>Creados</small><strong>${item.created}</strong></div>
                        <div class="count-card warn"><small>Duplicados</small><strong>${item.duplicates}</strong></div>
                        <div class="count-card bad"><small>Errores</small><strong>${item.errors}</strong></div>
                    </div>
                    <p style="font-size:.78rem;color:var(--muted);margin-bottom:.6rem">Mapeo usado: ${Object.entries(item.mapping || {}).map(([field, header]) => `<span class="chip">${esc(FIELDS.find((f) => f.key === field)?.label || field)} → ${esc(header)}</span>`).join(' ') || 'Sin mapeo'}</p>
                    <div class="issues">
                        ${item.issues?.length ? item.issues.map((issue) => `<div class="issue"><span class="n">Fila ${issue.row}</span><span class="type ${issue.type}">${issue.type === 'duplicate' ? 'Duplicada' : issue.type === 'warning' ? 'Aviso' : 'Error'}</span><span>${esc(issue.message)}</span></div>`).join('') : '<div class="issue"><span>Sin incidencias en este archivo.</span></div>'}
                    </div>
                </div>
                <div class="modal-foot"><span class="spacer"></span><button class="btn ghost" data-action="close-modal">Cerrar</button></div>
            </div>
        `);
    }

    function handleCsvFile(file) {
        if (!file) return;
        if (!/\.(csv|txt)$/i.test(file.name)) {
            toast('Elige un archivo CSV o TXT.', 'danger');
            return;
        }
        const reader = new FileReader();
        reader.onload = () => {
            try {
                const parsed = parseCsv(String(reader.result || ''));
                if (!parsed.rows.length) throw new Error('El archivo no tiene filas de datos.');
                if (parsed.rows.length > 500) throw new Error('En la demo puedes importar hasta 500 filas por archivo.');
                ui.wizard = {
                    step: 2,
                    fileName: file.name,
                    headers: parsed.headers,
                    rows: parsed.rows,
                    mapping: autoMap(parsed.headers),
                    analysis: null,
                };
                refreshWizard();
            } catch (error) {
                toast(error.message || 'No se pudo leer el archivo.', 'danger');
            }
        };
        reader.onerror = () => toast('No se pudo leer el archivo.', 'danger');
        reader.readAsText(file, 'UTF-8');
    }

    function exportCsv() {
        const rows = filteredContacts();
        const headers = ['Nombre', 'Correo', 'Teléfono', 'Empresa', 'Cargo', 'Tipo de cliente', 'Tipo de negocio', 'Género', 'Ciudad', 'Provincia', 'País', 'Fecha de nacimiento', 'Origen', 'Marca asociada', 'Empresa asociada', 'Segmentos'];
        const lines = [headers.map(csvEscapeCell).join(',')];
        rows.forEach((contact) => {
            lines.push([
                contact.name, contact.email, contact.phone, contact.company, contact.job_title,
                contact.contact_type === 'B2B' ? 'B2B · Empresa' : contact.contact_type === 'B2C' ? 'B2C · Persona' : '',
                contact.business_type, contact.gender, contact.city, contact.province, contact.country,
                contact.birthdate ? formatDate(contact.birthdate) : '',
                contact.source, contact.associated_brand, contact.associated_company,
                contact.segments.map((id) => segmentById(id)?.name).filter(Boolean).join(', '),
            ].map(csvEscapeCell).join(','));
        });
        const blob = new Blob([`\uFEFF${lines.join('\r\n')}`], { type: 'text/csv;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `contactos-${todayISO()}.csv`;
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(url);
        toast(`Exportados ${rows.length} contactos.`);
    }

    function downloadSample() {
        const sample = [
            'Origen del contacto,B2B / B2C,Genero,Nombre y Apellido,Correo,Teléfono celular / Whatsapp,Empresa,Tipo de negocio (Hotel, Proveedor, ONG),Cargo,Ciudad,Provincia,Pais,Cumpleaños / Fecha de Nacimiento,Marca Asociada,Empresa Asociada',
            'Feria,B2B,Femenino,María López,maria.demo@example.test,+507 6000-1111,"Hotel, Playa",Hotel,Gerente,Panamá,Panamá,Panamá,12/04/1985,Marca Sol,Hotel Playa',
            'Web,B2C,Masculino,Carlos Ruiz,carlos.demo@example.test,+507 6000-2222,,,Diseñador,David,Chiriquí,Panamá,03/09/1992,,',
            'Referido,B2B,Femenino,Ana Villalaz,ana.demo@example.test,+507 6000-3333,ONG Raíces,ONG,Directora,Panamá,Panamá,Panamá,28/11/1979,,',
            'Instagram,B2C,Otro,Sofía Méndez,sofia.demo@example.test,+507 6000-4444,,,Fotógrafa,Colón,Colón,Panamá,15/06/1996,,',
            'Evento,B2B,Masculino,Jorge Ríos,jorge.demo@example.test,+507 6000-5555,Grupo Mar,Proveedor,Jefe de compras,Colón,Colón,Panamá,22/01/1988,,',
            'Web,B2C,,Sin Teléfono,sin.telefono@example.test,,,,,,Panamá,Panamá,,,,',
            'Feria,B2B,Femenino,Fila Duplicada,maria.demo@example.test,,Hotel Playa,Hotel,Gerente,Panamá,Panamá,Panamá,12/04/1985,Marca Sol,Hotel Playa',
        ].join('\r\n');
        const blob = new Blob([`\uFEFF${sample}`], { type: 'text/csv;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'contactos-ejemplo.csv';
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(url);
        toast('CSV de ejemplo descargado.');
    }

    /* ---------- Actividad ---------- */

    function screenActivity() {
        const events = [
            ['', 'Todo'], ['created', 'Creaciones'], ['updated', 'Ediciones'], ['deleted', 'Eliminaciones'],
            ['note_added', 'Notas'], ['imported', 'Importaciones'], ['login', 'Accesos'],
        ];
        const logs = state.activity.filter((item) => !ui.activityFilter || item.event === ui.activityFilter);
        const groups = logs.reduce((acc, item) => {
            const day = item.created_at.slice(0, 10);
            (acc[day] ||= []).push(item);
            return acc;
        }, {});
        const dayLabel = (day) => {
            if (day === todayISO()) return 'Hoy';
            const yesterday = addDays(new Date(), -1).toISOString().slice(0, 10);
            if (day === yesterday) return 'Ayer';
            return formatDate(day, { weekday: 'long', day: 'numeric', month: 'long' });
        };
        return `
            <div class="page-head">
                <div><p class="eyebrow">Trazabilidad</p><h1>Actividad</h1><p class="muted">Quién creó, editó, importó o anotó. El historial se guarda junto a cada contacto.</p></div>
                <div class="head-actions"><select class="select" id="activity-filter" aria-label="Filtrar actividad">${events.map(([value, label]) => `<option value="${value}" ${ui.activityFilter === value ? 'selected' : ''}>${label}</option>`).join('')}</select></div>
            </div>
            ${Object.entries(groups).map(([day, items]) => `
                <div class="day-group">
                    <p class="day-label">${esc(dayLabel(day))}</p>
                    <div class="card activity-card">
                        ${items.map((item) => `<div class="activity-row">
                            <span class="feed-icon">${icon(ICONS[item.event] || 'history')}</span>
                            <div><p>${esc(item.description)}</p><small>${esc(userById(item.user_id)?.name || 'Alguien')} · ${relativeTime(item.created_at)}</small></div>
                            ${item.subject_type === 'contact' && item.subject_id && contactById(item.subject_id) ? `<button class="btn small" data-contact-open="${item.subject_id}">${icon('eye')} Ver</button>` : ''}
                            ${item.subject_type === 'import' && item.subject_id ? `<button class="btn small" data-import-view="${item.subject_id}">${icon('doc')} Informe</button>` : ''}
                        </div>`).join('')}
                    </div>
                </div>
            `).join('') || `<div class="card empty">${icon('history')}<strong>Sin actividad</strong><p>Cuando el equipo empiece a moverse, lo verás aquí.</p></div>`}
        `;
    }

    /* ---------- Usuarios ---------- */

    function screenUsers() {
        if (!canManageUsers()) {
            return `<div class="card empty">${icon('shield')}<strong>Solo para administración</strong><p>Este perfil no puede gestionar usuarios.</p></div>`;
        }
        return `
            <div class="page-head">
                <div><p class="eyebrow">Equipo</p><h1>Usuarios</h1><p class="muted">Tres roles comprobados: administración, edición y lectura. Añade personas o cambia sus permisos.</p></div>
                <div class="head-actions"><button class="btn primary" data-action="new-user">${icon('plus')} Nuevo usuario</button></div>
            </div>
            <div class="role-legend">
                <article class="card legend"><span class="stat-icon clay">${icon('shield')}</span><div><strong>Administración</strong><p>Gestiona todo, incluidos usuarios y borrado de contactos.</p></div></article>
                <article class="card legend"><span class="stat-icon sage">${icon('edit')}</span><div><strong>Edición</strong><p>Crea y edita contactos, segmentos e importaciones.</p></div></article>
                <article class="card legend"><span class="stat-icon gold">${icon('eye')}</span><div><strong>Lectura</strong><p>Consulta, filtra y exporta sin modificar datos.</p></div></article>
            </div>
            <div class="card table-wrap">
                <table class="data">
                    <thead><tr><th>Persona</th><th>Correo</th><th>Rol</th><th></th></tr></thead>
                    <tbody>
                        ${state.users.map((user) => `<tr>
                            <td><div class="cell-person">${avatar(user.name)}<div><strong>${esc(user.name)}${user.id === currentUser().id ? ' <span class="cell-muted">(tú)</span>' : ''}</strong><small>Miembro de la demo</small></div></div></td>
                            <td class="cell-muted">${esc(user.email)}</td>
                            <td><select class="select" data-user-role="${user.id}" ${user.id === currentUser().id ? 'disabled' : ''} aria-label="Rol de ${esc(user.name)}">
                                <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Administración</option>
                                <option value="editor" ${user.role === 'editor' ? 'selected' : ''}>Edición</option>
                                <option value="viewer" ${user.role === 'viewer' ? 'selected' : ''}>Lectura</option>
                            </select></td>
                            <td><div class="row-actions">${user.id !== currentUser().id ? `<button class="icon-btn" data-user-delete="${user.id}" aria-label="Eliminar ${esc(user.name)}">${icon('trash')}</button>` : ''}</div></td>
                        </tr>`).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    function userForm() {
        openModal(`
            <form class="modal" data-form="user">
                <div class="modal-head"><div><h2>Nuevo usuario</h2><p>En la demo solo se guarda en tu navegador.</p></div><button type="button" class="icon-btn" data-action="close-modal" aria-label="Cerrar">${icon('x')}</button></div>
                <div class="modal-body"><div class="form-grid">
                    <div class="field full"><label for="u-name">Nombre</label><input id="u-name" name="name" required maxlength="120"></div>
                    <div class="field full"><label for="u-email">Correo</label><input id="u-email" name="email" type="email" required maxlength="190"></div>
                    <div class="field full"><label for="u-role">Rol</label><select id="u-role" name="role"><option value="viewer">Lectura</option><option value="editor">Edición</option><option value="admin">Administración</option></select></div>
                    <div class="field full" id="user-error" hidden><p class="error-text"></p></div>
                </div></div>
                <div class="modal-foot"><span class="spacer"></span><button type="button" class="btn ghost" data-action="close-modal">Cancelar</button><button type="submit" class="btn primary">${icon('check')} Crear usuario</button></div>
            </form>
        `);
    }

    function saveUser(form) {
        const data = new FormData(form);
        const name = String(data.get('name') || '').trim();
        const email = String(data.get('email') || '').trim().toLowerCase();
        const errorBox = $('#user-error', form);
        const showError = (message) => { errorBox.hidden = false; $('.error-text', errorBox).textContent = message; };
        if (!name) return showError('El nombre es obligatorio.');
        if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) return showError('El correo no es válido.');
        if (state.users.some((user) => user.email === email)) return showError('Ese correo ya está en uso.');
        state.users.push({ id: uid('u'), name, email, role: String(data.get('role') || 'viewer') });
        logActivity('created', `Creó el usuario ${name}`, 'user', null);
        saveState();
        closeModal();
        render();
        toast('Usuario creado.');
    }

    function changeUserRole(id, role) {
        const user = userById(id);
        if (!user || !canManageUsers() || user.id === currentUser().id) return;
        if (!['admin', 'editor', 'viewer'].includes(role)) return;
        if (user.role === 'admin' && state.users.filter((item) => item.role === 'admin').length <= 1) {
            toast('Debe quedar al menos una persona administradora.', 'danger');
            render();
            return;
        }
        user.role = role;
        logActivity('updated', `Cambió el rol de ${user.name}`, 'user', user.id);
        saveState();
        render();
        toast('Rol actualizado.');
    }

    function deleteUser(id) {
        const user = userById(id);
        if (!user || !canManageUsers() || user.id === currentUser().id) return;
        if (user.role === 'admin' && state.users.filter((item) => item.role === 'admin').length <= 1) {
            toast('Debe quedar al menos una persona administradora.', 'danger');
            return;
        }
        confirmModal('Eliminar usuario', `¿Eliminar a ${esc(user.name)}? Sus registros de actividad se conservarán.`, () => {
            state.users = state.users.filter((item) => item.id !== id);
            saveState();
            closeModal();
            render();
            toast('Usuario eliminado.');
        });
    }

    /* ---------- Ajustes ---------- */

    function screenSettings() {
        const theme = document.documentElement.dataset.theme;
        return `
            <div class="page-head"><div><p class="eyebrow">Cuenta</p><h1>Ajustes</h1><p class="muted">Preferencias de esta maqueta y opciones para volver a empezar.</p></div></div>
            <div class="settings-grid">
                <div class="stack">
                    <section class="card">
                        <div class="section-title"><div><h2>Apariencia</h2><small>La preferencia se guarda en este navegador.</small></div></div>
                        <div class="setting-row">
                            <div><strong>Tema</strong><p>Cambia entre claro, oscuro o lo que prefiera el sistema.</p></div>
                            <div class="segmented">${[['light', 'sun', 'Claro'], ['dark', 'moon', 'Oscuro'], ['system', 'sparkle', 'Sistema']].map(([value, ico, label]) => `<button data-theme-choice="${value}" class="${theme === value ? 'active' : ''}">${icon(ico)} ${label}</button>`).join('')}</div>
                        </div>
                        <div class="setting-row">
                            <div><strong>Exportar respaldo</strong><p>Descarga todos los contactos visibles en CSV con escape de fórmulas.</p></div>
                            <button class="btn" data-action="export-csv">${icon('download')} Exportar</button>
                        </div>
                    </section>

                    <section class="card">
                        <div class="section-title"><div><h2>Perfil de demo</h2><small>Estás explorando como ${esc(ROLES[session.role].label)}.</small></div></div>
                        <div class="setting-row">
                            <div><strong>${esc(currentUser().name)}</strong><p>${esc(currentUser().email)} · ${esc(ROLES[session.role].description || '')}</p></div>
                            <span class="role-chip">${esc(ROLES[session.role].short)}</span>
                        </div>
                        <div class="setting-row">
                            <div><strong>Cambiar de perfil</strong><p>Vuelve a la pantalla de entrada para probar otra rol.</p></div>
                            <button class="btn" data-action="logout">${icon('logout')} Salir</button>
                        </div>
                    </section>

                    <section class="card">
                        <div class="section-title"><div><h2>Datos de la demo</h2><small>${state.contacts.length} contactos · ${state.segments.length} segmentos · ${state.notes.length} notas</small></div></div>
                        <div class="setting-row">
                            <div><strong>Restablecer datos</strong><p>Vuelve a los 48 contactos ficticios originales. Se pierden los cambios de este navegador.</p></div>
                            <button class="btn danger" data-action="reset-demo">${icon('reset')} Restablecer</button>
                        </div>
                    </section>
                </div>
                <aside class="card about-card">
                    <strong>Gestor de contactos · demo estática</strong>
                    <p>Esta versión funciona sin servidor: la sesión, los contactos y el historial viven en <code>localStorage</code>.</p>
                    <p>La app real está construida con Laravel 12, Livewire 3, Flux 2 y PostgreSQL, y usa colas para archivos grandes.</p>
                    <p>Atajos: <span class="kbd">Esc</span> cierra un diálogo. El tema y la sesión persisten al recargar.</p>
                    <a class="section-link" href="https://github.com/Zobercito/gestor-de-contactos" target="_blank" rel="noopener">Ver el repositorio ${icon('arrow-right')}</a>
                </aside>
            </div>
        `;
    }

    /* ---------- Modales ---------- */

    function openModal(html) {
        const root = $('#modal-root');
        root.innerHTML = `<div class="modal-backdrop" data-action="backdrop">${html}</div>`;
        document.body.style.overflow = 'hidden';
        const first = root.querySelector('input, select, textarea, button');
        if (first) first.focus({ preventScroll: true });
    }

    function closeModal() {
        $('#modal-root').innerHTML = '';
        document.body.style.overflow = '';
        ui.wizard = null;
    }

    function confirmModal(title, message, onConfirm) {
        openModal(`
            <div class="modal" style="max-width:480px">
                <div class="modal-head"><div><h2>${title}</h2><p>Esta acción no se puede deshacer en la demo.</p></div><button class="icon-btn" data-action="close-modal" aria-label="Cerrar">${icon('x')}</button></div>
                <div class="modal-body"><p style="font-size:.86rem;line-height:1.6;color:var(--muted)">${message}</p></div>
                <div class="modal-foot"><span class="spacer"></span><button class="btn ghost" data-action="close-modal">Cancelar</button><button class="btn danger" id="confirm-action">Confirmar</button></div>
            </div>
        `);
        $('#confirm-action').addEventListener('click', onConfirm, { once: true });
    }

    /* ---------- Tema / sidebar ---------- */

    function applyTheme(theme) {
        const resolved = theme === 'system' ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') : theme;
        document.documentElement.dataset.theme = resolved;
        localStorage.setItem(THEME_KEY, theme);
        $('#theme-toggle').innerHTML = icon(resolved === 'dark' ? 'sun' : 'moon');
    }

    function toggleTheme() {
        const current = localStorage.getItem(THEME_KEY) || 'light';
        const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        localStorage.setItem(THEME_KEY, next);
        applyTheme(next);
        if (ui.view === 'settings') render();
        else toast(next === 'dark' ? 'Tema oscuro activado.' : 'Tema claro activado.');
    }

    function closeSidebar() {
        $('#sidebar')?.classList.remove('open');
        const scrim = $('#sidebar-scrim');
        if (scrim) scrim.hidden = true;
    }

    function openSidebar() {
        $('#sidebar')?.classList.add('open');
        const scrim = $('#sidebar-scrim');
        if (scrim) scrim.hidden = false;
    }

    function resetDemo() {
        confirmModal('Restablecer la demo', 'Se borrarán los contactos, notas, segmentos e importaciones creados en este navegador y volverán los datos iniciales.', () => {
            localStorage.removeItem(STATE_KEY);
            localStorage.removeItem(SESSION_KEY);
            localStorage.removeItem(THEME_KEY);
            location.reload();
        });
    }

    /* ---------- Eventos ---------- */

    document.addEventListener('click', (event) => {
        const target = event.target.closest('[data-enter], [data-view], [data-action], [data-contact-open], [data-contact-edit], [data-contact-delete], [data-segment-view], [data-segment-edit], [data-segment-delete], [data-import-view], [data-note-delete], [data-page], [data-clear], [data-color], [data-theme-choice], [data-user-delete], [data-action="backdrop"]');
        if (!target) return;

        if (target.dataset.enter) { login(target.dataset.enter); return; }
        if (target.dataset.view) { go(target.dataset.view); return; }

        if (target.dataset.action === 'backdrop' && event.target === target) { closeModal(); return; }

        const action = target.dataset.action;
        if (action) {
            switch (action) {
                case 'close-modal': closeModal(); break;
                case 'new-contact': if (canWrite()) contactForm(); else toast('Tu perfil es de lectura.', 'danger'); break;
                case 'logout': logout(); break;
                case 'reset-demo': resetDemo(); break;
                case 'toggle-theme': toggleTheme(); break;
                case 'export-csv': exportCsv(); break;
                case 'download-sample': downloadSample(); break;
                case 'new-import': if (canWrite()) importModal(); else toast('Tu perfil es de lectura.', 'danger'); break;
                case 'pick-csv': $('#csv-input').click(); break;
                case 'wizard-reset': importModal(); break;
                case 'wizard-back': ui.wizard.step = Math.max(1, ui.wizard.step - 1); refreshWizard(); break;
                case 'wizard-analyze': analyzeWizard(); break;
                case 'wizard-confirm': confirmImport(); break;
                case 'new-segment': if (canWrite()) segmentForm(); else toast('Tu perfil es de lectura.', 'danger'); break;
                case 'new-user': if (canManageUsers()) userForm(); break;
                default: break;
            }
            return;
        }

        if (target.dataset.contactOpen) { contactModal(target.dataset.contactOpen); return; }
        if (target.dataset.contactEdit) { if (canWrite()) contactForm(contactById(target.dataset.contactEdit)); else toast('Tu perfil es de lectura.', 'danger'); return; }
        if (target.dataset.contactDelete) { deleteContact(target.dataset.contactDelete); return; }

        if (target.dataset.segmentView) { ui.contactSegment = target.dataset.segmentView; ui.view = 'contacts'; ui.contactPage = 1; history.replaceState(null, '', '#contacts'); render(); return; }
        if (target.dataset.segmentEdit) { if (canWrite()) segmentForm(segmentById(target.dataset.segmentEdit)); return; }
        if (target.dataset.segmentDelete) { deleteSegment(target.dataset.segmentDelete); return; }

        if (target.dataset.importView) { openImportReport(target.dataset.importView); return; }
        if (target.dataset.noteDelete) {
            if (!canWrite()) return;
            const note = state.notes.find((item) => item.id === target.dataset.noteDelete);
            if (!note) return;
            if (session.role !== 'admin' && note.user_id !== currentUser().id) { toast('Solo puedes borrar tus propias notas.', 'danger'); return; }
            state.notes = state.notes.filter((item) => item.id !== note.id);
            saveState();
            const contactId = note.contact_id;
            contactModal(contactId);
            toast('Nota eliminada.');
            return;
        }

        if (target.dataset.page) { ui.contactPage = Math.max(1, Number(target.dataset.page)); render(); return; }
        if (target.dataset.clear) {
            if (target.dataset.clear === 'all') { ui.contactSearch = ''; ui.contactSegment = ''; ui.contactType = ''; }
            if (target.dataset.clear === 'search') ui.contactSearch = '';
            if (target.dataset.clear === 'segment') ui.contactSegment = '';
            if (target.dataset.clear === 'type') ui.contactType = '';
            ui.contactPage = 1;
            render();
            return;
        }
        if (target.dataset.color) {
            const form = target.closest('form');
            $('input[name="color"]', form).value = target.dataset.color;
            $$('.color-dot', form).forEach((dot) => dot.classList.toggle('active', dot.dataset.color === target.dataset.color));
            return;
        }
        if (target.dataset.themeChoice) {
            applyTheme(target.dataset.themeChoice);
            render();
            return;
        }
        if (target.dataset.userDelete) { deleteUser(target.dataset.userDelete); return; }
        if (target.closest('#open-sidebar')) { openSidebar(); return; }
        if (target.closest('#close-sidebar')) { closeSidebar(); }
    });

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('form[data-form]');
        if (!form) return;
        event.preventDefault();
        if (form.dataset.form === 'contact') saveContact(form);
        if (form.dataset.form === 'segment') saveSegment(form);
        if (form.dataset.form === 'user') saveUser(form);
        if (form.dataset.form === 'add-note') {
            if (!canWrite()) { toast('Tu perfil es de lectura.', 'danger'); return; }
            const body = String(new FormData(form).get('body') || '').trim();
            if (body.length < 2) return;
            const contactId = form.dataset.contact;
            state.notes.push({ id: uid('n'), contact_id: contactId, user_id: currentUser().id, body, created_at: new Date().toISOString() });
            const contact = contactById(contactId);
            logActivity('note_added', `Añadió una nota a ${contact?.name || 'un contacto'}`, 'contact', contactId);
            saveState();
            contactModal(contactId);
            toast('Nota añadida.');
        }
    });

    document.addEventListener('input', (event) => {
        if (event.target.id === 'contact-search') {
            ui.contactSearch = event.target.value;
            ui.contactPage = 1;
            render();
            const input = $('#contact-search');
            if (input) { input.focus(); input.setSelectionRange(input.value.length, input.value.length); }
        }
    });

    document.addEventListener('change', (event) => {
        if (event.target.id === 'filter-segment') { ui.contactSegment = event.target.value; ui.contactPage = 1; render(); }
        if (event.target.id === 'filter-type') { ui.contactType = event.target.value; ui.contactPage = 1; render(); }
        if (event.target.id === 'activity-filter') { ui.activityFilter = event.target.value; render(); }
        if (event.target.dataset.userRole) { changeUserRole(event.target.dataset.userRole, event.target.value); }
        if (event.target.dataset.map) {
            ui.wizard.mapping[event.target.dataset.map] = event.target.value;
        }
        if (event.target.closest('#dropzone') && event.target.type === 'file') { handleCsvFile(event.target.files[0]); }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        if ($('#modal-root .modal')) closeModal();
        else closeSidebar();
    });

    $('#csv-input').addEventListener('change', (event) => {
        handleCsvFile(event.target.files[0]);
        event.target.value = '';
    });

    // Drag & drop del CSV
    document.addEventListener('dragover', (event) => {
        const zone = event.target.closest('#dropzone');
        if (zone) { event.preventDefault(); zone.classList.add('drag'); }
    });
    document.addEventListener('dragleave', (event) => {
        event.target.closest('#dropzone')?.classList.remove('drag');
    });
    document.addEventListener('drop', (event) => {
        const zone = event.target.closest('#dropzone');
        if (!zone) return;
        event.preventDefault();
        zone.classList.remove('drag');
        handleCsvFile(event.dataTransfer.files[0]);
    });

    $('#open-sidebar').addEventListener('click', openSidebar);
    $('#close-sidebar').addEventListener('click', closeSidebar);
    $('#sidebar-scrim').addEventListener('click', closeSidebar);

    // Tema inicial
    const storedTheme = localStorage.getItem(THEME_KEY) || 'light';
    applyTheme(storedTheme);

    // Arranque
    if (session) {
        $('#login-view').hidden = true;
        $('#app-view').hidden = false;
        go(location.hash.replace('#', '') || 'dashboard');
    } else {
        $('#login-view').hidden = false;
        $('#app-view').hidden = true;
    }
})();
