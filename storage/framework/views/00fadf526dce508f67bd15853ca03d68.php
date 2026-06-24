<?php $__env->startSection('title', 'Staff'); ?>
<?php $__env->startSection('page_title', 'Staff'); ?>

<?php $__env->startSection('content'); ?>
<?php
    // Common administrative/academic positions found across Philippine college
    // departments/offices. Kept generic since these roles repeat across every
    // college type (College of Science, College of Education, etc.).
    $commonPositions = [
        'Dean',
        'Associate Dean',
        'Department Chairperson',
        'Program Coordinator',
        'College Secretary',
        'Administrative Officer',
        'Administrative Aide',
        'Records Officer',
        'Guidance Counselor',
        'Librarian',
        'Library Assistant',
        'Registrar Staff',
        'Faculty / Instructor',
        'Assistant Professor',
        'Associate Professor',
        'Professor',
        'Laboratory Technician',
        'IT / MIS Staff',
        'Property Custodian',
        'Budget Officer',
        'Accounting Staff',
        'Cashier',
        'Human Resources Officer',
        'Clerk',
        'Utility Worker',
        'Security Guard',
        'Driver',
    ];

    // Resolve a stored/old position string into [selectValue, otherText] so
    // the dropdown can pre-select a known option, or fall back to "Other".
    $resolvePosition = function (?string $value) use ($commonPositions) {
        if ($value === null || $value === '') {
            return ['', ''];
        }

        if (in_array($value, $commonPositions, true)) {
            return [$value, ''];
        }

        return ['__other__', $value];
    };

    $addBag = $errors->getBag('add');
    $editBag = $errors->getBag('edit');

    [$addSinglePosition, $addSinglePositionOther] = $resolvePosition(old('position'));
    [$editPosition, $editPositionOther] = $resolvePosition(old('position'));

    $oldStaffRows = old('staff', []);
    $bulkSeedCount = $oldStaffRows ? max(1, min(3, count($oldStaffRows))) : 2;

    $bulkRowsSeed = [];
    for ($i = 0; $i < $bulkSeedCount; $i++) {
        [$rowPosition, $rowPositionOther] = $resolvePosition($oldStaffRows[$i]['position'] ?? null);

        $bulkRowsSeed[] = [
            'first_name' => $oldStaffRows[$i]['first_name'] ?? '',
            'last_name' => $oldStaffRows[$i]['last_name'] ?? '',
            'position' => $rowPosition,
            'positionOther' => $rowPositionOther,
            'email' => $oldStaffRows[$i]['email'] ?? '',
            'phone' => $oldStaffRows[$i]['phone'] ?? '',
            'is_active' => $oldStaffRows ? isset($oldStaffRows[$i]['is_active']) : true,
            'firstNameError' => $addBag->first("staff.$i.first_name"),
            'lastNameError' => $addBag->first("staff.$i.last_name"),
            'emailError' => $addBag->first("staff.$i.email"),
            'phoneError' => $addBag->first("staff.$i.phone"),
        ];
    }
?>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('staffManager', () => ({
        addOpen: <?php echo e($addBag->any() ? 'true' : 'false'); ?>,
        editOpen: <?php echo e($editBag->any() ? 'true' : 'false'); ?>,
        deleteOpen: false,
        bulkEnabled: <?php echo e(old('staff') !== null ? 'true' : 'false'); ?>,

        commonPositions: <?php echo json_encode($commonPositions, 15, 512) ?>,

        // Resolve a position string into {position, positionOther} for the
        // dropdown — used when opening Edit on an existing staff record.
        resolvePosition(value) {
            if (!value) {
                return { position: '', positionOther: '' };
            }
            if (this.commonPositions.includes(value)) {
                return { position: value, positionOther: '' };
            }
            return { position: '__other__', positionOther: value };
        },

        addSingle: {
            first_name: <?php echo \Illuminate\Support\Js::from(old('first_name', ''))->toHtml() ?>,
            last_name: <?php echo \Illuminate\Support\Js::from(old('last_name', ''))->toHtml() ?>,
            position: <?php echo \Illuminate\Support\Js::from($addSinglePosition)->toHtml() ?>,
            positionOther: <?php echo \Illuminate\Support\Js::from($addSinglePositionOther)->toHtml() ?>,
            email: <?php echo \Illuminate\Support\Js::from(old('email', ''))->toHtml() ?>,
            phone: <?php echo \Illuminate\Support\Js::from(old('phone', ''))->toHtml() ?>,
            is_active: <?php echo e(old('first_name') !== null ? (old('is_active') ? 'true' : 'false') : 'true'); ?>,
            firstNameError: <?php echo \Illuminate\Support\Js::from($addBag->first('first_name'))->toHtml() ?>,
            lastNameError: <?php echo \Illuminate\Support\Js::from($addBag->first('last_name'))->toHtml() ?>,
            emailError: <?php echo \Illuminate\Support\Js::from($addBag->first('email'))->toHtml() ?>,
            phoneError: <?php echo \Illuminate\Support\Js::from($addBag->first('phone'))->toHtml() ?>
        },

        bulkRows: <?php echo json_encode($bulkRowsSeed, 15, 512) ?>,

        editStaff: {
            id: <?php echo \Illuminate\Support\Js::from(old('editing_id') !== null ? (int) old('editing_id') : null)->toHtml() ?>,
            first_name: <?php echo \Illuminate\Support\Js::from(old('first_name', ''))->toHtml() ?>,
            last_name: <?php echo \Illuminate\Support\Js::from(old('last_name', ''))->toHtml() ?>,
            position: <?php echo \Illuminate\Support\Js::from($editPosition)->toHtml() ?>,
            positionOther: <?php echo \Illuminate\Support\Js::from($editPositionOther)->toHtml() ?>,
            email: <?php echo \Illuminate\Support\Js::from(old('email', ''))->toHtml() ?>,
            phone: <?php echo \Illuminate\Support\Js::from(old('phone', ''))->toHtml() ?>,
            is_active: <?php echo e(old('editing_id') !== null ? (old('is_active') ? 'true' : 'false') : 'true'); ?>,
            firstNameError: <?php echo \Illuminate\Support\Js::from($editBag->first('first_name'))->toHtml() ?>,
            lastNameError: <?php echo \Illuminate\Support\Js::from($editBag->first('last_name'))->toHtml() ?>,
            emailError: <?php echo \Illuminate\Support\Js::from($editBag->first('email'))->toHtml() ?>,
            phoneError: <?php echo \Illuminate\Support\Js::from($editBag->first('phone'))->toHtml() ?>
        },

        deleteStaffId: null,

        blankRow() {
            return {
                first_name: '', last_name: '', position: '', positionOther: '', email: '', phone: '',
                is_active: true,
                firstNameError: '', lastNameError: '', emailError: '', phoneError: ''
            };
        },

        openAdd() {
            this.addOpen = true;
            this.bulkEnabled = false;
            this.addSingle = this.blankRow();
            this.bulkRows = [this.blankRow(), this.blankRow()];
        },

        addBulkRow() {
            if (this.bulkRows.length < 3) {
                this.bulkRows.push(this.blankRow());
            }
        },

        removeBulkRow() {
            if (this.bulkRows.length > 1) {
                this.bulkRows.pop();
            }
        },

        openEdit(staff) {
            const resolved = this.resolvePosition(staff.position ?? '');

            this.editStaff = {
                id: staff.id,
                first_name: staff.first_name ?? '',
                last_name: staff.last_name ?? '',
                position: resolved.position,
                positionOther: resolved.positionOther,
                email: staff.email ?? '',
                phone: staff.phone ?? '',
                is_active: !!staff.is_active,
                firstNameError: '',
                lastNameError: '',
                emailError: '',
                phoneError: ''
            };
            this.editOpen = true;
        },

        openDelete(id) {
            this.deleteStaffId = id;
            this.deleteOpen = true;
            this.$nextTick(() => this.$refs.confirmDeleteBtn && this.$refs.confirmDeleteBtn.focus());
        }
    }));
});
</script>
<div
    x-data="staffManager"
    class="space-y-5"
>
    
    <div class="text-sm text-gray-500 leading-6 break-words">
        <a class="text-blue-600 hover:underline" href="<?php echo e(route('admin.colleges.index')); ?>">Colleges</a>
        <span class="mx-1">/</span>
        <a class="text-blue-600 hover:underline" href="<?php echo e(route('admin.offices.index', $office->college)); ?>">
            <?php echo e($office->college->name); ?>

        </a>
        <span class="mx-1">/</span>
        <span class="text-gray-700 font-medium"><?php echo e($office->name); ?></span>
        <span class="mx-1">/</span>
        <span>Staff</span>
    </div>

    
    <div class="flex items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Staff in <?php echo e($office->name); ?></h1>
        </div>

        <div class="flex flex-wrap gap-2">
            <a
                href="<?php echo e(route('admin.offices.preventiveMaintenance.export', $office)); ?>"
                class="shrink-0 inline-flex items-center rounded-xl bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700"
            >
                Export Excel Report
            </a>

            <button
                type="button"
                class="shrink-0 inline-flex items-center rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700"
                @click="openAdd()"
            >
                + Add Staff
            </button>
        </div>
    </div>

    
    <div class="grid grid-cols-1 gap-3 md:hidden">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $staff; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <a
                            class="font-semibold text-blue-700 hover:underline"
                            href="<?php echo e(route('admin.staff.devices.index', $s)); ?>"
                        >
                            <?php echo e($s->last_name); ?>, <?php echo e($s->first_name); ?>

                        </a>

                        <div class="mt-1 text-sm text-gray-500">
                            <?php echo e($s->position ?: 'No position set'); ?>

                        </div>
                    </div>

                    <div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s->is_active): ?>
                            <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700">
                                Active
                            </span>
                        <?php else: ?>
                            <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">
                                Inactive
                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-3 text-sm">
                    <div>
                        <div class="text-gray-500">Email</div>
                        <div class="break-all text-gray-900"><?php echo e($s->email ?: '-'); ?></div>
                    </div>

                    <div>
                        <div class="text-gray-500">Phone</div>
                        <div class="text-gray-900"><?php echo e($s->phone ?: '-'); ?></div>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <a
                        href="<?php echo e(route('admin.staff.devices.index', $s)); ?>"
                        class="rounded-lg bg-green-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-700"
                    >
                        Devices
                    </a>

                    <button
                        type="button"
                        class="rounded-lg bg-gray-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-black"
                        @click="openEdit({
                            id: <?php echo e($s->id); ?>,
                            first_name: <?php echo \Illuminate\Support\Js::from($s->first_name)->toHtml() ?>,
                            last_name: <?php echo \Illuminate\Support\Js::from($s->last_name)->toHtml() ?>,
                            position: <?php echo \Illuminate\Support\Js::from($s->position ?? '')->toHtml() ?>,
                            email: <?php echo \Illuminate\Support\Js::from($s->email ?? '')->toHtml() ?>,
                            phone: <?php echo \Illuminate\Support\Js::from($s->phone ?? '')->toHtml() ?>,
                            is_active: <?php echo e($s->is_active ? 'true' : 'false'); ?>

                        })"
                    >
                        Edit
                    </button>

                    <button
                        type="button"
                        class="rounded-lg bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700"
                        @click="openDelete(<?php echo e($s->id); ?>)"
                    >
                        Delete
                    </button>
                </div>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <div class="rounded-2xl border border-gray-200 bg-white p-6 text-center text-gray-500 shadow-sm">
                No staff found.
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div class="hidden md:block overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left">
                    <tr>
                        <th class="px-4 py-3 font-semibold text-gray-700">Name</th>
                        <th class="px-4 py-3 font-semibold text-gray-700">Position</th>
                        <th class="px-4 py-3 font-semibold text-gray-700">Email</th>
                        <th class="px-4 py-3 font-semibold text-gray-700">Phone</th>
                        <th class="px-4 py-3 font-semibold text-gray-700">Status</th>
                        <th class="px-4 py-3 font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $staff; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <a
                                    class="font-medium text-blue-700 hover:underline"
                                    href="<?php echo e(route('admin.staff.devices.index', $s)); ?>"
                                >
                                    <?php echo e($s->last_name); ?>, <?php echo e($s->first_name); ?>

                                </a>
                            </td>

                            <td class="px-4 py-3 text-gray-700"><?php echo e($s->position ?: '-'); ?></td>
                            <td class="px-4 py-3 text-gray-700"><?php echo e($s->email ?: '-'); ?></td>
                            <td class="px-4 py-3 text-gray-700"><?php echo e($s->phone ?: '-'); ?></td>

                            <td class="px-4 py-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($s->is_active): ?>
                                    <span class="inline-flex rounded-full bg-green-100 px-2.5 py-1 text-xs font-medium text-green-700">
                                        Active
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">
                                        Inactive
                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>

                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <a
                                        href="<?php echo e(route('admin.staff.devices.index', $s)); ?>"
                                        class="rounded-lg bg-green-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-700"
                                    >
                                        Devices
                                    </a>

                                    <button
                                        type="button"
                                        class="rounded-lg bg-gray-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-black"
                                        @click="openEdit({
                                            id: <?php echo e($s->id); ?>,
                                            first_name: <?php echo \Illuminate\Support\Js::from($s->first_name)->toHtml() ?>,
                                            last_name: <?php echo \Illuminate\Support\Js::from($s->last_name)->toHtml() ?>,
                                            position: <?php echo \Illuminate\Support\Js::from($s->position ?? '')->toHtml() ?>,
                                            email: <?php echo \Illuminate\Support\Js::from($s->email ?? '')->toHtml() ?>,
                                            phone: <?php echo \Illuminate\Support\Js::from($s->phone ?? '')->toHtml() ?>,
                                            is_active: <?php echo e($s->is_active ? 'true' : 'false'); ?>

                                        })"
                                    >
                                        Edit
                                    </button>

                                    <button
                                        type="button"
                                        class="rounded-lg bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700"
                                        @click="openDelete(<?php echo e($s->id); ?>)"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                No staff found.
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <?php echo e($staff->links()); ?>

    </div>

    
    <?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['show' => 'addOpen','title' => 'Add Staff']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['show' => 'addOpen','title' => 'Add Staff']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <form method="POST" action="<?php echo e(route('admin.staff.store', $office)); ?>" class="space-y-3">
            <?php echo csrf_field(); ?>

            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">Add multiple staff</span>
                <button
                    type="button"
                    class="rounded-lg px-3 py-1.5 text-sm font-medium border"
                    :class="bulkEnabled ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300'"
                    @click="bulkEnabled = !bulkEnabled"
                >
                    <span x-text="bulkEnabled ? 'Bulk: On' : 'Bulk: Off'"></span>
                </button>
            </div>

            <div class="space-y-3">
                <!-- Bulk controls -->
                <div x-show="bulkEnabled" class="flex items-center gap-2">
                    <button
                        type="button"
                        class="rounded-lg bg-gray-100 px-3 py-1.5 text-gray-700 hover:bg-gray-200"
                        @click="removeBulkRow()"
                    >-
                    </button>

                    <div class="text-sm text-gray-700">
                        Records: <span class="font-semibold" x-text="bulkRows.length"></span>
                    </div>

                    <button
                        type="button"
                        class="rounded-lg bg-gray-100 px-3 py-1.5 text-gray-700 hover:bg-gray-200"
                        @click="addBulkRow()"
                    >+
                    </button>
                </div>

                <!-- Bulk form -->
                <template x-if="bulkEnabled">
                    <div class="space-y-5">
                        <template x-for="(row, idx) in bulkRows" :key="idx">
                            <div class="space-y-3" :class="idx > 0 ? 'pt-4 border-t border-gray-200' : ''">
                                <div>
                                    <label class="text-sm font-medium">First Name</label>
                                    <input
                                        :name="`staff[${idx}][first_name]`"
                                        x-model="row.first_name"
                                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                                        required
                                        maxlength="100"
                                        pattern="[A-Za-zÑñ][A-Za-zÑñ.\-'\s]*"
                                        title="Letters only (no numbers or symbols other than . - ')"
                                        placeholder="e.g. Juan"
                                        @input="row.first_name = row.first_name.replace(/[^A-Za-zÑñ.\-'\s]/g, '')"
                                    >
                                    <div class="mt-1 text-sm text-red-600" x-show="row.firstNameError" x-text="row.firstNameError"></div>
                                </div>

                                <div>
                                    <label class="text-sm font-medium">Last Name</label>
                                    <input
                                        :name="`staff[${idx}][last_name]`"
                                        x-model="row.last_name"
                                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                                        required
                                        maxlength="100"
                                        pattern="[A-Za-zÑñ][A-Za-zÑñ.\-'\s]*"
                                        title="Letters only (no numbers or symbols other than . - ')"
                                        placeholder="e.g. Dela Cruz"
                                        @input="row.last_name = row.last_name.replace(/[^A-Za-zÑñ.\-'\s]/g, '')"
                                    >
                                    <div class="mt-1 text-sm text-red-600" x-show="row.lastNameError" x-text="row.lastNameError"></div>
                                </div>

                                <div>
                                    <label class="text-sm font-medium">Position</label>
                                    <select
                                        x-model="row.position"
                                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                                    >
                                        <option value="">Select position</option>
                                        <template x-for="opt in commonPositions" :key="opt">
                                            <option :value="opt" x-text="opt"></option>
                                        </template>
                                        <option value="__other__">Other (please specify)</option>
                                    </select>

                                    <input
                                        type="hidden"
                                        :name="`staff[${idx}][position]`"
                                        :value="row.position === '__other__' ? row.positionOther : row.position"
                                    >

                                    <input
                                        x-show="row.position === '__other__'"
                                        x-model="row.positionOther"
                                        type="text"
                                        maxlength="100"
                                        class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2"
                                        placeholder="Specify position"
                                    >
                                </div>

                                <div>
                                    <label class="text-sm font-medium">Email</label>
                                    <input
                                        :name="`staff[${idx}][email]`"
                                        x-model="row.email"
                                        type="email"
                                        maxlength="255"
                                        pattern="[^\s@]+@[^\s@]+\.[^\s@]+"
                                        title="Enter a complete email address, e.g. juan.delacruz@example.com"
                                        placeholder="e.g. juan.delacruz@example.com"
                                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                                    >
                                    <div class="mt-1 text-sm text-red-600" x-show="row.emailError" x-text="row.emailError"></div>
                                </div>

                                <div>
                                    <label class="text-sm font-medium">Phone</label>
                                    <input
                                        :name="`staff[${idx}][phone]`"
                                        x-model="row.phone"
                                        inputmode="numeric"
                                        maxlength="11"
                                        pattern="09[0-9]{9}"
                                        title="11-digit PH mobile number starting with 09, e.g. 09171234567"
                                        placeholder="09XXXXXXXXX"
                                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                                        @input="row.phone = row.phone.replace(/[^0-9]/g, '').slice(0, 11)"
                                    >
                                    <div class="mt-1 text-sm text-red-600" x-show="row.phoneError" x-text="row.phoneError"></div>
                                </div>

                                <label class="flex items-center gap-2 text-sm">
                                    <input
                                        type="checkbox"
                                        :name="`staff[${idx}][is_active]`"
                                        value="1"
                                        x-model="row.is_active"
                                    >
                                    Active
                                </label>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Single form -->
                <template x-if="!bulkEnabled">
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium">First Name</label>
                            <input
                                name="first_name"
                                x-model="addSingle.first_name"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                                required
                                maxlength="100"
                                pattern="[A-Za-zÑñ][A-Za-zÑñ.\-'\s]*"
                                title="Letters only (no numbers or symbols other than . - ')"
                                placeholder="e.g. Juan"
                                @input="addSingle.first_name = addSingle.first_name.replace(/[^A-Za-zÑñ.\-'\s]/g, '')"
                            >
                            <div class="mt-1 text-sm text-red-600" x-show="addSingle.firstNameError" x-text="addSingle.firstNameError"></div>
                        </div>

                        <div>
                            <label class="text-sm font-medium">Last Name</label>
                            <input
                                name="last_name"
                                x-model="addSingle.last_name"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                                required
                                maxlength="100"
                                pattern="[A-Za-zÑñ][A-Za-zÑñ.\-'\s]*"
                                title="Letters only (no numbers or symbols other than . - ')"
                                placeholder="e.g. Dela Cruz"
                                @input="addSingle.last_name = addSingle.last_name.replace(/[^A-Za-zÑñ.\-'\s]/g, '')"
                            >
                            <div class="mt-1 text-sm text-red-600" x-show="addSingle.lastNameError" x-text="addSingle.lastNameError"></div>
                        </div>

                        <div>
                            <label class="text-sm font-medium">Position</label>
                            <select
                                x-model="addSingle.position"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                            >
                                <option value="">Select position</option>
                                <template x-for="opt in commonPositions" :key="opt">
                                    <option :value="opt" x-text="opt"></option>
                                </template>
                                <option value="__other__">Other (please specify)</option>
                            </select>

                            <input type="hidden" name="position" :value="addSingle.position === '__other__' ? addSingle.positionOther : addSingle.position">

                            <input
                                x-show="addSingle.position === '__other__'"
                                x-model="addSingle.positionOther"
                                type="text"
                                maxlength="100"
                                class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2"
                                placeholder="Specify position"
                            >
                        </div>

                        <div>
                            <label class="text-sm font-medium">Email</label>
                            <input
                                name="email"
                                type="email"
                                x-model="addSingle.email"
                                maxlength="255"
                                pattern="[^\s@]+@[^\s@]+\.[^\s@]+"
                                title="Enter a complete email address, e.g. juan.delacruz@example.com"
                                placeholder="e.g. juan.delacruz@example.com"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                            >
                            <div class="mt-1 text-sm text-red-600" x-show="addSingle.emailError" x-text="addSingle.emailError"></div>
                        </div>

                        <div>
                            <label class="text-sm font-medium">Phone</label>
                            <input
                                name="phone"
                                x-model="addSingle.phone"
                                inputmode="numeric"
                                maxlength="11"
                                pattern="09[0-9]{9}"
                                title="11-digit PH mobile number starting with 09, e.g. 09171234567"
                                placeholder="09XXXXXXXXX"
                                class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                                @input="addSingle.phone = addSingle.phone.replace(/[^0-9]/g, '').slice(0, 11)"
                            >
                            <div class="mt-1 text-sm text-red-600" x-show="addSingle.phoneError" x-text="addSingle.phoneError"></div>
                        </div>

                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="is_active" value="1" x-model="addSingle.is_active">
                            Active
                        </label>
                    </div>
                </template>
            </div>

            <div class="flex gap-2 pt-2">
                <button class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Save</button>
                <button type="button" class="rounded-lg bg-gray-100 px-4 py-2 text-gray-700 hover:bg-gray-200" @click="addOpen=false">Cancel</button>
            </div>
        </form>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $attributes = $__attributesOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__attributesOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $component = $__componentOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__componentOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>

    
    <?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['show' => 'editOpen','title' => 'Edit Staff']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['show' => 'editOpen','title' => 'Edit Staff']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <form
            method="POST"
            :action="`<?php echo e(url('/offices/'.$office->id.'/staff')); ?>/${editStaff.id}`"
            class="space-y-3"
        >
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <input type="hidden" name="editing_id" :value="editStaff.id">

            <div>
                <label class="text-sm font-medium">First Name</label>
                <input
                    name="first_name"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                    x-model="editStaff.first_name"
                    required
                    maxlength="100"
                    pattern="[A-Za-zÑñ][A-Za-zÑñ.\-'\s]*"
                    title="Letters only (no numbers or symbols other than . - ')"
                    @input="editStaff.first_name = editStaff.first_name.replace(/[^A-Za-zÑñ.\-'\s]/g, '')"
                >
                <div class="mt-1 text-sm text-red-600" x-show="editStaff.firstNameError" x-text="editStaff.firstNameError"></div>
            </div>

            <div>
                <label class="text-sm font-medium">Last Name</label>
                <input
                    name="last_name"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                    x-model="editStaff.last_name"
                    required
                    maxlength="100"
                    pattern="[A-Za-zÑñ][A-Za-zÑñ.\-'\s]*"
                    title="Letters only (no numbers or symbols other than . - ')"
                    @input="editStaff.last_name = editStaff.last_name.replace(/[^A-Za-zÑñ.\-'\s]/g, '')"
                >
                <div class="mt-1 text-sm text-red-600" x-show="editStaff.lastNameError" x-text="editStaff.lastNameError"></div>
            </div>

            <div>
                <label class="text-sm font-medium">Position</label>
                <select
                    x-model="editStaff.position"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                >
                    <option value="">Select position</option>
                    <template x-for="opt in commonPositions" :key="opt">
                        <option :value="opt" x-text="opt"></option>
                    </template>
                    <option value="__other__">Other (please specify)</option>
                </select>

                <input type="hidden" name="position" :value="editStaff.position === '__other__' ? editStaff.positionOther : editStaff.position">

                <input
                    x-show="editStaff.position === '__other__'"
                    x-model="editStaff.positionOther"
                    type="text"
                    maxlength="100"
                    class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2"
                    placeholder="Specify position"
                >
            </div>

            <div>
                <label class="text-sm font-medium">Email</label>
                <input
                    name="email"
                    type="email"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                    x-model="editStaff.email"
                    maxlength="255"
                    pattern="[^\s@]+@[^\s@]+\.[^\s@]+"
                    title="Enter a complete email address, e.g. juan.delacruz@example.com"
                >
                <div class="mt-1 text-sm text-red-600" x-show="editStaff.emailError" x-text="editStaff.emailError"></div>
            </div>

            <div>
                <label class="text-sm font-medium">Phone</label>
                <input
                    name="phone"
                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2"
                    x-model="editStaff.phone"
                    inputmode="numeric"
                    maxlength="11"
                    pattern="09[0-9]{9}"
                    title="11-digit PH mobile number starting with 09, e.g. 09171234567"
                    placeholder="09XXXXXXXXX"
                    @input="editStaff.phone = editStaff.phone.replace(/[^0-9]/g, '').slice(0, 11)"
                >
                <div class="mt-1 text-sm text-red-600" x-show="editStaff.phoneError" x-text="editStaff.phoneError"></div>
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" x-model="editStaff.is_active">
                Active
            </label>

            <div class="flex gap-2 pt-2">
                <button class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Update</button>
                <button type="button" class="rounded-lg bg-gray-100 px-4 py-2 text-gray-700 hover:bg-gray-200" @click="editOpen=false">Cancel</button>
            </div>
        </form>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $attributes = $__attributesOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__attributesOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $component = $__componentOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__componentOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>

    
    <?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['show' => 'deleteOpen','title' => 'Delete Staff']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['show' => 'deleteOpen','title' => 'Delete Staff']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

        <div class="space-y-3">
            <div class="text-sm text-gray-700">
                Are you sure you want to delete this staff member?
            </div>

            <form
                method="POST"
                :action="`<?php echo e(url('/offices/'.$office->id.'/staff')); ?>/${deleteStaffId}`"
                @submit="if (!deleteStaffId) $event.preventDefault()"
                class="flex gap-2"
            >
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>

                <button type="submit" x-ref="confirmDeleteBtn" class="rounded-lg bg-red-600 px-4 py-2 text-white hover:bg-red-700">Confirm</button>
                <button type="button" class="rounded-lg bg-gray-100 px-4 py-2 text-gray-700 hover:bg-gray-200" @click="deleteOpen=false">Cancel</button>
            </form>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $attributes = $__attributesOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__attributesOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $component = $__componentOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__componentOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\PMS_system\resources\views/admin/staff/index.blade.php ENDPATH**/ ?>