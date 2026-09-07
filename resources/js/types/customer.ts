export interface Customer {
    id: number;
    name: string;
    email: string;
    image: string | null;
    phone: string | null;
    company: string | null;
    address: string | null;
    status: 'active' | 'inactive';
    notes: string | null;
    created_at: string;
    updated_at: string;
}

export interface CustomerFilters {
    search?: string;
    status?: string;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}
