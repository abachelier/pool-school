import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import PasswordSetupController from '@/actions/App/Http/Controllers/Auth/PasswordSetupController';

type Props = {
    passwordRules: string;
};

export default function SetupPassword({ passwordRules }: Props) {
    return (
        <>
            <Head title="Set your password" />

            <Form
                {...PasswordSetupController.store.form()}
                resetOnError={['password', 'password_confirmation']}
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-6">
                            <p className="text-muted-foreground text-sm">
                                Welcome! Please set a password for your account
                                to continue.
                            </p>

                            <div className="grid gap-2">
                                <Label htmlFor="password">Password</Label>
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    required
                                    autoFocus
                                    autoComplete="new-password"
                                    placeholder="Password"
                                    passwordrules={passwordRules}
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">
                                    Confirm password
                                </Label>
                                <PasswordInput
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    required
                                    autoComplete="new-password"
                                    placeholder="Confirm password"
                                    passwordrules={passwordRules}
                                />
                                <InputError
                                    message={errors.password_confirmation}
                                />
                            </div>

                            <Button
                                type="submit"
                                className="mt-4 w-full"
                                disabled={processing}
                            >
                                {processing && <Spinner />}
                                Set password
                            </Button>
                        </div>
                    </>
                )}
            </Form>
        </>
    );
}

SetupPassword.layout = {
    title: 'Set your password',
    description: 'Choose a password for your account',
};
