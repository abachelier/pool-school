import { Head, useForm } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { store } from '@/routes/schools/sessions';
import type { School } from '@/types';

type PageProps = {
    school: School;
};

export default function SessionsCreate({
    school,
}: PageProps) {
    const { data, setData, post, processing, errors } = useForm({
        date: new Date().toISOString().split('T')[0],
        notes: '',
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post(store.url(school.id));
    }

    return (
        <>
            <Head title="New Session" />

            <div className="space-y-6 p-4">
                <Heading
                    title="New Session"
                    description="Create a new training session."
                />

                <form
                    onSubmit={submit}
                    className="max-w-lg space-y-6"
                >
                    <div className="grid gap-2">
                        <Label htmlFor="date">Date</Label>
                        <Input
                            id="date"
                            type="date"
                            value={data.date}
                            onChange={(e) => setData('date', e.target.value)}
                            required
                        />
                        <InputError message={errors.date} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="notes">
                            Notes{' '}
                            <span className="text-muted-foreground">
                                (optional)
                            </span>
                        </Label>
                        <textarea
                            id="notes"
                            className="border-input bg-background placeholder:text-muted-foreground focus-visible:ring-ring flex min-h-[80px] w-full rounded-md border px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            value={data.notes}
                            onChange={(e) => setData('notes', e.target.value)}
                            placeholder="Any notes about this session..."
                        />
                        <InputError message={errors.notes} />
                    </div>

                    <div className="flex items-center gap-4">
                        <Button disabled={processing}>Create Session</Button>
                    </div>
                </form>
            </div>
        </>
    );
}
