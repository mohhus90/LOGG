@foreach($nodes as $node)
<div class="org-node" style="margin-right: {{ $depth * 30 }}px">
    <div class="org-node-card">
        <span class="badge bg-secondary me-1">{{ $node->level_order }}</span>
        <strong>{{ $node->name }}</strong>
        @if($node->name_en)
            <small class="text-muted">({{ $node->name_en }})</small>
        @endif
        {!! $node->level_type_badge !!}
        @if($node->receives_seller_commission)
            <span class="badge bg-success" title="عمولة بائع"><i class="fas fa-dollar-sign"></i> بائع</span>
        @endif
        @if($node->receives_manager_commission)
            <span class="badge bg-primary" title="عمولة مدير"><i class="fas fa-user-tie"></i> مدير</span>
        @endif
        <span class="badge bg-light text-dark">{{ $node->jobs()->count() }} وظيفة</span>
        <a href="{{ route('org_levels.edit', $node->id) }}" class="btn btn-xs btn-warning py-0 px-1">
            <i class="fas fa-edit"></i>
        </a>
    </div>
    @if(!empty($node->children_tree))
        @include('admin.org_levels._tree_node', ['nodes' => $node->children_tree, 'depth' => $depth + 1])
    @endif
</div>
@endforeach
