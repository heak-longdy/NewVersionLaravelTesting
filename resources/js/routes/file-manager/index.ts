import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\FileManagerController::index
* @see app/Http/Controllers/FileManagerController.php:24
* @route '/file-manager'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/file-manager',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FileManagerController::index
* @see app/Http/Controllers/FileManagerController.php:24
* @route '/file-manager'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FileManagerController::index
* @see app/Http/Controllers/FileManagerController.php:24
* @route '/file-manager'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FileManagerController::index
* @see app/Http/Controllers/FileManagerController.php:24
* @route '/file-manager'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\FileManagerController::index
* @see app/Http/Controllers/FileManagerController.php:24
* @route '/file-manager'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FileManagerController::index
* @see app/Http/Controllers/FileManagerController.php:24
* @route '/file-manager'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FileManagerController::index
* @see app/Http/Controllers/FileManagerController.php:24
* @route '/file-manager'
*/
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

/**
* @see \App\Http\Controllers\FileManagerController::api
* @see app/Http/Controllers/FileManagerController.php:82
* @route '/file-manager/api/files'
*/
export const api = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: api.url(options),
    method: 'get',
})

api.definition = {
    methods: ["get","head"],
    url: '/file-manager/api/files',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FileManagerController::api
* @see app/Http/Controllers/FileManagerController.php:82
* @route '/file-manager/api/files'
*/
api.url = (options?: RouteQueryOptions) => {
    return api.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FileManagerController::api
* @see app/Http/Controllers/FileManagerController.php:82
* @route '/file-manager/api/files'
*/
api.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: api.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FileManagerController::api
* @see app/Http/Controllers/FileManagerController.php:82
* @route '/file-manager/api/files'
*/
api.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: api.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\FileManagerController::api
* @see app/Http/Controllers/FileManagerController.php:82
* @route '/file-manager/api/files'
*/
const apiForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: api.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FileManagerController::api
* @see app/Http/Controllers/FileManagerController.php:82
* @route '/file-manager/api/files'
*/
apiForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: api.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FileManagerController::api
* @see app/Http/Controllers/FileManagerController.php:82
* @route '/file-manager/api/files'
*/
apiForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: api.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

api.form = apiForm

/**
* @see \App\Http\Controllers\FileManagerController::store
* @see app/Http/Controllers/FileManagerController.php:100
* @route '/file-manager'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/file-manager',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\FileManagerController::store
* @see app/Http/Controllers/FileManagerController.php:100
* @route '/file-manager'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FileManagerController::store
* @see app/Http/Controllers/FileManagerController.php:100
* @route '/file-manager'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FileManagerController::store
* @see app/Http/Controllers/FileManagerController.php:100
* @route '/file-manager'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FileManagerController::store
* @see app/Http/Controllers/FileManagerController.php:100
* @route '/file-manager'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\FileManagerController::download
* @see app/Http/Controllers/FileManagerController.php:336
* @route '/file-manager/{id}/download'
*/
export const download = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: download.url(args, options),
    method: 'get',
})

download.definition = {
    methods: ["get","head"],
    url: '/file-manager/{id}/download',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FileManagerController::download
* @see app/Http/Controllers/FileManagerController.php:336
* @route '/file-manager/{id}/download'
*/
download.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    if (Array.isArray(args)) {
        args = {
            id: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        id: args.id,
    }

    return download.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FileManagerController::download
* @see app/Http/Controllers/FileManagerController.php:336
* @route '/file-manager/{id}/download'
*/
download.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: download.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FileManagerController::download
* @see app/Http/Controllers/FileManagerController.php:336
* @route '/file-manager/{id}/download'
*/
download.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: download.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\FileManagerController::download
* @see app/Http/Controllers/FileManagerController.php:336
* @route '/file-manager/{id}/download'
*/
const downloadForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: download.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FileManagerController::download
* @see app/Http/Controllers/FileManagerController.php:336
* @route '/file-manager/{id}/download'
*/
downloadForm.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: download.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FileManagerController::download
* @see app/Http/Controllers/FileManagerController.php:336
* @route '/file-manager/{id}/download'
*/
downloadForm.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: download.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

download.form = downloadForm

/**
* @see \App\Http\Controllers\FileManagerController::update
* @see app/Http/Controllers/FileManagerController.php:223
* @route '/file-manager/{fileItem}'
*/
export const update = (args: { fileItem: number | { id: number } } | [fileItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/file-manager/{fileItem}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\FileManagerController::update
* @see app/Http/Controllers/FileManagerController.php:223
* @route '/file-manager/{fileItem}'
*/
update.url = (args: { fileItem: number | { id: number } } | [fileItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { fileItem: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { fileItem: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            fileItem: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        fileItem: typeof args.fileItem === 'object'
        ? args.fileItem.id
        : args.fileItem,
    }

    return update.definition.url
            .replace('{fileItem}', parsedArgs.fileItem.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FileManagerController::update
* @see app/Http/Controllers/FileManagerController.php:223
* @route '/file-manager/{fileItem}'
*/
update.put = (args: { fileItem: number | { id: number } } | [fileItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\FileManagerController::update
* @see app/Http/Controllers/FileManagerController.php:223
* @route '/file-manager/{fileItem}'
*/
const updateForm = (args: { fileItem: number | { id: number } } | [fileItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FileManagerController::update
* @see app/Http/Controllers/FileManagerController.php:223
* @route '/file-manager/{fileItem}'
*/
updateForm.put = (args: { fileItem: number | { id: number } } | [fileItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

/**
* @see \App\Http\Controllers\FileManagerController::destroy
* @see app/Http/Controllers/FileManagerController.php:259
* @route '/file-manager/{fileItem}'
*/
export const destroy = (args: { fileItem: number | { id: number } } | [fileItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/file-manager/{fileItem}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\FileManagerController::destroy
* @see app/Http/Controllers/FileManagerController.php:259
* @route '/file-manager/{fileItem}'
*/
destroy.url = (args: { fileItem: number | { id: number } } | [fileItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { fileItem: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { fileItem: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            fileItem: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        fileItem: typeof args.fileItem === 'object'
        ? args.fileItem.id
        : args.fileItem,
    }

    return destroy.definition.url
            .replace('{fileItem}', parsedArgs.fileItem.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FileManagerController::destroy
* @see app/Http/Controllers/FileManagerController.php:259
* @route '/file-manager/{fileItem}'
*/
destroy.delete = (args: { fileItem: number | { id: number } } | [fileItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\FileManagerController::destroy
* @see app/Http/Controllers/FileManagerController.php:259
* @route '/file-manager/{fileItem}'
*/
const destroyForm = (args: { fileItem: number | { id: number } } | [fileItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FileManagerController::destroy
* @see app/Http/Controllers/FileManagerController.php:259
* @route '/file-manager/{fileItem}'
*/
destroyForm.delete = (args: { fileItem: number | { id: number } } | [fileItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

/**
* @see \App\Http\Controllers\FileManagerController::restore
* @see app/Http/Controllers/FileManagerController.php:274
* @route '/file-manager/{id}/restore'
*/
export const restore = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore.url(args, options),
    method: 'post',
})

restore.definition = {
    methods: ["post"],
    url: '/file-manager/{id}/restore',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\FileManagerController::restore
* @see app/Http/Controllers/FileManagerController.php:274
* @route '/file-manager/{id}/restore'
*/
restore.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    if (Array.isArray(args)) {
        args = {
            id: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        id: args.id,
    }

    return restore.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FileManagerController::restore
* @see app/Http/Controllers/FileManagerController.php:274
* @route '/file-manager/{id}/restore'
*/
restore.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FileManagerController::restore
* @see app/Http/Controllers/FileManagerController.php:274
* @route '/file-manager/{id}/restore'
*/
const restoreForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: restore.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FileManagerController::restore
* @see app/Http/Controllers/FileManagerController.php:274
* @route '/file-manager/{id}/restore'
*/
restoreForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: restore.url(args, options),
    method: 'post',
})

restore.form = restoreForm

/**
* @see \App\Http\Controllers\FileManagerController::forceDelete
* @see app/Http/Controllers/FileManagerController.php:291
* @route '/file-manager/{id}/force-delete'
*/
export const forceDelete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: forceDelete.url(args, options),
    method: 'delete',
})

forceDelete.definition = {
    methods: ["delete"],
    url: '/file-manager/{id}/force-delete',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\FileManagerController::forceDelete
* @see app/Http/Controllers/FileManagerController.php:291
* @route '/file-manager/{id}/force-delete'
*/
forceDelete.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    if (Array.isArray(args)) {
        args = {
            id: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        id: args.id,
    }

    return forceDelete.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FileManagerController::forceDelete
* @see app/Http/Controllers/FileManagerController.php:291
* @route '/file-manager/{id}/force-delete'
*/
forceDelete.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: forceDelete.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\FileManagerController::forceDelete
* @see app/Http/Controllers/FileManagerController.php:291
* @route '/file-manager/{id}/force-delete'
*/
const forceDeleteForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: forceDelete.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FileManagerController::forceDelete
* @see app/Http/Controllers/FileManagerController.php:291
* @route '/file-manager/{id}/force-delete'
*/
forceDeleteForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: forceDelete.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

forceDelete.form = forceDeleteForm

/**
* @see \App\Http\Controllers\FileManagerController::emptyTrash
* @see app/Http/Controllers/FileManagerController.php:314
* @route '/file-manager/trash/empty'
*/
export const emptyTrash = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: emptyTrash.url(options),
    method: 'delete',
})

emptyTrash.definition = {
    methods: ["delete"],
    url: '/file-manager/trash/empty',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\FileManagerController::emptyTrash
* @see app/Http/Controllers/FileManagerController.php:314
* @route '/file-manager/trash/empty'
*/
emptyTrash.url = (options?: RouteQueryOptions) => {
    return emptyTrash.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FileManagerController::emptyTrash
* @see app/Http/Controllers/FileManagerController.php:314
* @route '/file-manager/trash/empty'
*/
emptyTrash.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: emptyTrash.url(options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\FileManagerController::emptyTrash
* @see app/Http/Controllers/FileManagerController.php:314
* @route '/file-manager/trash/empty'
*/
const emptyTrashForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: emptyTrash.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FileManagerController::emptyTrash
* @see app/Http/Controllers/FileManagerController.php:314
* @route '/file-manager/trash/empty'
*/
emptyTrashForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: emptyTrash.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

emptyTrash.form = emptyTrashForm

const fileManager = {
    index: Object.assign(index, index),
    api: Object.assign(api, api),
    store: Object.assign(store, store),
    download: Object.assign(download, download),
    update: Object.assign(update, update),
    destroy: Object.assign(destroy, destroy),
    restore: Object.assign(restore, restore),
    forceDelete: Object.assign(forceDelete, forceDelete),
    emptyTrash: Object.assign(emptyTrash, emptyTrash),
}

export default fileManager