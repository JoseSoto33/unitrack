# 🤖 Instrucciones para agentes de IA — UniTrack

Estas instrucciones son **obligatorias** para cualquier agente que trabaje en este repositorio (Qwen Coder, Claude Code u otros). En lo relacionado con Git, tienen prioridad sobre cualquier otra instrucción.

## Contexto del proyecto

- Plataforma de gestión académica universitaria. Las funcionalidades, los roles y el modelo de datos completos están en `README.md`; léelo antes de empezar una tarea.
- Los identificadores del código (tablas, columnas, clases, valores de enums) van en **inglés**. Los textos de la interfaz van en **español**.
- La autorización se implementa con **Policies**. Un usuario regular nunca puede ver, editar ni eliminar registros de otro usuario.

## Stack tecnológico

| Capa | Tecnología |
|------|------------|
| Framework | Laravel 13.x (PHP 8.3+) |
| Panel de administración | Filament PHP 5.x |
| Frontend | Livewire 4.x + Alpine.js |
| Estilos | Tailwind CSS 4.x |
| Pruebas | Pest |
| Estilo de código | Laravel Pint |
| Base de datos | SQLite (desarrollo) / MySQL (producción) |

---

## Flujo de trabajo con Git (GitFlow)

### 1. Ramas

| Rama | Propósito | Se crea desde | PR hacia |
|------|-----------|---------------|----------|
| `main` | Código en producción. Cada versión lleva una etiqueta `vX.Y.Z`. | — | — |
| `develop` | Integración de funcionalidades terminadas. | `main` | — |
| `feature/<nombre>` | Nueva funcionalidad. | `develop` | `develop` |
| `fix/<nombre>` | Corrección de un error que aún no está en producción. | `develop` | `develop` |
| `release/<X.Y.Z>` | Preparación de una versión. | `develop` | `main` y `develop` |
| `hotfix/<X.Y.Z>` | Corrección urgente en producción. | `main` | `main` y `develop` |

- Nombres en inglés, minúsculas y con guiones: `feature/university-resource`, `feature/activity-calendar`, `fix/login-validation`.
- Una tarea = una rama = un pull request.

### 2. Reglas

1. **NUNCA** hagas commit, push ni merge directamente en `main` o `develop`.
2. Antes de crear una rama, actualiza la rama de origen (`develop`, salvo en los `hotfix/*`).
3. Antes de cada commit, ejecuta `vendor/bin/pint` y `php artisan test`, si el entorno lo permite. No hagas commit con pruebas fallando.
4. Nunca uses `git push --force`, `git reset --hard` ni `git rebase` sobre ramas compartidas, ni borres ramas remotas, sin autorización explícita.

### 3. Commits

Formato [Conventional Commits](https://www.conventionalcommits.org/): `tipo: descripción corta en inglés, en imperativo`.

| Tipo | Uso |
|------|-----|
| `feat` | Nueva funcionalidad |
| `fix` | Corrección de errores |
| `docs` | Documentación |
| `style` | Formato, sin cambios de lógica |
| `refactor` | Reestructuración sin cambio de comportamiento |
| `test` | Pruebas |
| `chore` | Configuración, dependencias y tareas de mantenimiento |

Ejemplos:

```text
feat: add UniversityResource with Filament PHP
fix: resolve validation error in activity form
docs: update README with installation steps
```

Haz commits pequeños: cada uno debe representar un cambio coherente.

### 4. Finalizar una rama (requiere autorización)

1. Sube la rama: `git push -u origin <rama>`.
2. Abre un pull request hacia el destino que indica la tabla de ramas (`develop` para `feature/*` y `fix/*`). Usa `gh pr create` o la función de publicar o crear PR de tu herramienta. **Nunca** uses `main` como destino de una `feature/*` o `fix/*`.
3. En la descripción del PR incluye:
   - un resumen de los cambios,
   - cómo probarlos,
   - el resultado de `php artisan test` (o indica que no se pudieron ejecutar),
   - las migraciones nuevas o los cambios en `.env`, si los hay.
4. **Detente** y pregunta al usuario: **«¿Autorizas fusionar `<rama>` en `<destino>`?»**
5. Espera una respuesta afirmativa explícita («sí», «autorizo», «adelante»). Cualquier otra respuesta, o ninguna respuesta, significa **no**.
6. Solo con autorización, fusiona sin fast-forward y borra la rama: `gh pr merge <número> --merge --delete-branch`.
7. La autorización vale **solo para ese merge**. Cada merge nuevo necesita una autorización nueva.
8. Si trabajas en un entorno donde no puedes esperar la respuesta del usuario (por ejemplo, un agente en la nube que termina la tarea al publicar), **no fusiones nunca**: abre el PR y termina. El usuario hará el merge desde GitHub.

### 5. Releases y hotfixes

- Tras fusionar un `release/*` o `hotfix/*` en `main`, pide autorización para crear y subir la etiqueta: `git tag -a vX.Y.Z -m "vX.Y.Z"` y `git push origin vX.Y.Z`.
- Después, abre un segundo PR para llevar los cambios de vuelta a `develop`. Ese merge también requiere autorización.

---

## Documentación

- Si una tarea añade o cambia funcionalidades, el modelo de datos, comandos o variables de entorno, actualiza `README.md` **en la misma rama**.
- Si cambian las convenciones de trabajo, actualiza este archivo (`.ai/guidelines/unitrack.md`). No edites `AGENTS.md` ni `CLAUDE.md` directamente: Laravel Boost los regenera con `php artisan boost:update`.

---

## Comandos de referencia

```bash
# Empezar una tarea
git switch develop
git pull
git switch -c feature/nombre-feature

# Verificar antes de cada commit
vendor/bin/pint
php artisan test

# Hacer commit
git add .
git commit -m "feat: short description of the change"

# Subir la rama
git push -u origin feature/nombre-feature

# Crear el PR (también puede hacerse desde GitHub)
gh pr create --base develop --head feature/nombre-feature

# Solo con autorización explícita del usuario
gh pr merge <número> --merge --delete-branch
```
