<?php

namespace App\Http\Controllers\Backoffice;

use App\Actions\Backoffice\BuildBackofficeCrudModulePayloadAction;
use App\Actions\Backoffice\BuildBackofficePhase6CrudPayloadAction;
use App\Actions\Backoffice\SaveBackofficePhase6ModuleAction;
use App\Actions\Newsletter\QueueNewsletterCampaign;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\BackofficePreviewActionRequest;
use App\Http\Requests\Backoffice\BackofficePreviewDraftRequest;
use App\Http\Requests\Backoffice\BackofficePreviewIndexRequest;
use App\Models\NewsletterCampaign;
use App\Support\Backoffice\BackofficePath;
use App\Support\Backoffice\Phase6ModuleCatalog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PreviewController extends Controller
{
    public function __construct(
        private readonly BuildBackofficeCrudModulePayloadAction $crudPayload,
        private readonly BuildBackofficePhase6CrudPayloadAction $phase6Payload,
        private readonly SaveBackofficePhase6ModuleAction $savePhase6Module,
        private readonly QueueNewsletterCampaign $queueNewsletterCampaign,
    ) {
    }

    public function index(BackofficePreviewIndexRequest $request, string $module): Response
    {
        /** @var User $user */
        $user = $request->user();

        $payload = Phase6ModuleCatalog::supports($module)
            ? $this->phase6Payload->index($user, $module, $request->validated())
            : $this->crudPayload->index($user, $module, $request->validated());

        return Inertia::render('Backoffice/Preview/ModuleIndex', $payload);
    }

    public function create(Request $request, string $module): Response
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->canManageBackofficeContent(), 403);
        abort_if(Phase6ModuleCatalog::supports($module) && Phase6ModuleCatalog::isReadOnly($module), 403);

        $payload = Phase6ModuleCatalog::supports($module)
            ? $this->phase6Payload->form(
                $user,
                $module,
                'create',
                null,
                array_merge($request->query(), $request->session()->getOldInput()),
            )
            : $this->crudPayload->form(
                $user,
                $module,
                'create',
                null,
                $request->session()->getOldInput(),
            );

        return Inertia::render('Backoffice/Preview/ModuleForm', $payload);
    }

    public function edit(Request $request, string $module, string $record): Response
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless(
            Phase6ModuleCatalog::supports($module) && Phase6ModuleCatalog::isReadOnly($module)
                ? $user->canViewBackofficeContent()
                : $user->canManageBackofficeContent(),
            403,
        );

        $payload = Phase6ModuleCatalog::supports($module)
            ? $this->phase6Payload->form($user, $module, 'edit', $record, $request->session()->getOldInput())
            : $this->crudPayload->form(
                $user,
                $module,
                'edit',
                $record,
                $request->session()->getOldInput(),
            );

        return Inertia::render('Backoffice/Preview/ModuleForm', $payload);
    }

    public function draft(BackofficePreviewDraftRequest $request, string $module, ?string $record = null)
    {
        if (Phase6ModuleCatalog::supports($module)) {
            abort_if(Phase6ModuleCatalog::isReadOnly($module), 403);

            $saved = $this->savePhase6Module->execute($module, $request->validated(), $record);

            return redirect(BackofficePath::active($module.'/'.$saved->getKey().'/edit'))
                ->with('success', $record
                    ? Phase6ModuleCatalog::module($module)['singular'].' actualizado correctamente.'
                    : Phase6ModuleCatalog::module($module)['singular'].' creado correctamente.');
        }

        return redirect()->back()->with('success', $record
            ? 'Edicion validada correctamente en la superficie oficial del backoffice.'
            : 'Creacion validada correctamente en la superficie oficial del backoffice.');
    }

    public function action(BackofficePreviewActionRequest $request, string $module, string $action)
    {
        if ($module === 'newsletter-campaigns' && $action === 'queue-campaign') {
            $campaign = NewsletterCampaign::query()->findOrFail((string) $request->validated('record'));

            abort_unless(in_array($campaign->status, ['draft', 'cancelled'], true), 422);

            $this->queueNewsletterCampaign->execute($campaign);

            return redirect(BackofficePath::active('newsletter-campaigns/'.$campaign->getKey().'/edit'))
                ->with('success', 'Campana newsletter encolada correctamente.');
        }

        return redirect()->back()->with('success', 'Accion `'.$action.'` validada para `'.$module.'` en la superficie oficial del backoffice.');
    }
}
