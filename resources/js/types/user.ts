export interface UserItem {
    id: number;
    name: string;
    email: string;
    avatar?: string | null;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface UserFilters {
    search?: string;
}
