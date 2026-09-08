export type FileCategory =
    | 'all'
    | 'image'
    | 'document'
    | 'video'
    | 'audio'
    | 'archive'
    | 'code'
    | 'other';

export interface FileItem {
    id: number;
    user_id: number | null;
    name: string;
    original_name: string;
    file_path: string;
    disk: string;
    mime_type: string | null;
    extension: string | null;
    size: number;
    description: string | null;
    formatted_size: string;
    category: FileCategory;
    url: string;
    is_image: boolean;
    deleted_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface FileManagerFilters {
    tab: 'all' | 'trash';
    search?: string;
    category?: FileCategory;
    sort?: 'date_desc' | 'date_asc' | 'name_asc' | 'name_desc' | 'size_desc' | 'size_asc';
}

export interface FileManagerStats {
    total_files: number;
    trash_count: number;
    storage_used: number;
    storage_used_formatted: string;
}
